<?php
use \SimpleExcel\SimpleExcel;
require_once('./SimpleExcel/SimpleExcel.php');
class MyClass
{
    public $data;
    public static $count;
    public static $i;
    public static $collection;
    public $slice;
    public static $uploads_dir = '/var/www/awesome/uploads/';

    public function __construct()
    {

    }

    public function cmp($a, $b)
    {
        if(intval($a[self::$count]) == intval($b[self::$count]))
            return 0;
        return ((int)$a[self::$count] > (int)$b[self::$count])? 1: -1;
    }

    public function csvHandler(GearmanJob $job)
    {
        $mass = unserialize($job->workload());
        self::$count = count($mass[1])-1;
        usort($mass, (array('MyClass', 'cmp')));
        $off = array_slice($mass, 0, 1000);
        self::$collection[] = $off;
    }

    public function getCleanArr()
    {
        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator(self::$collection));
        $ita = iterator_to_array($iterator, false);
        $original = array_chunk($ita, self::$count+1);
        $ids = array_column($original, 0);
        $kill = array_count_values($ids);
        foreach ($kill as $key=>$value)
        {
            if($value>20)
            {
                $del = array_keys($ids, $key);
                foreach($del as $ita=>$delId)
                {
                    unset($original[$delId]);
                    unset($del[$ita]);
                    if(count($del) == 20)
                        break;
                }
            }
        }
        usort($original, (array('MyClass', 'cmp')));
        $cleanArr = array_slice($original, 0, 1000);
        return $cleanArr;
    }

    public static function executeBgTasks()
    {
        $files = scandir(self::$uploads_dir);
        unset ($files[0]);
        unset ($files[1]);
        $jobs = array_values($files);
        foreach($jobs as $key=>$singleJob)
        {
            $worker = new GearmanWorker();
            $worker->addServer();
            $worker->addFunction('q'.$key, array(new MyClass(new GearmanJob()), 'csvHandler'));
            $worker->work();
        }
    }

    public static function getFiles()
    {
        if(!empty($_FILES))
        {
            foreach ($_FILES["file"]["error"] as $key => $error)
            {
                if ($error == UPLOAD_ERR_OK)
                {
                        $tmp_name = $_FILES["file"]["tmp_name"][$key];
                        $name = $_FILES["file"]["name"][$key];
                        move_uploaded_file($tmp_name, self::$uploads_dir . $name);
                        $excel = new SimpleExcel('csv');
                        $excel->parser->loadFile(self::$uploads_dir . $name);
                        $data = $excel->parser->getField();
                        unset($data[0]);
                        $client = new GearmanClient();
                        $client->addServer();
                        $client->doBackground('q' . $key, serialize($data));
                }
            }
        }
    }

    public static function sendFile($file)
    {
        $excel = new SimpleExcel('csv');
        $excel->writer->setData($file);
        $excel->writer->setDelimiter(";");
        $excel->writer->saveFile('1000-20');
    }

    public static function rm()
    {
        if (file_exists(self::$uploads_dir))
            foreach (glob(self::$uploads_dir.'*') as $file)
                unlink($file);
    }


}