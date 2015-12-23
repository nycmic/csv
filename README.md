# csv
пример работы с многопоточностью Gearman для обработки табличных файлов

клон этот репо в любую папку.

-cd эта папка

-vagrant up

-попейте кофе пока установится окружение

в хостс прописать 192.168.56.101 awesome.dev www.awesome.dev

gearman нужно установить вручную через ssh:

-cd эта папка

-vagrant ssh

-sudo apt-get install gearman-job-server libgearman-dev

-sudo pecl install gearman

-exit

cd эта папка vagrant reload

gearman.so уже прописан
