#! /bin/sh

### BEGIN INIT INFO
# Provides:          bestdo_server
# Required-Start:    $remote_fs $network
# Required-Stop:     $remote_fs $network
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: starts bestdo_server
# Description:       starts the mail push server daemon
### END INIT INFO


#php路径，如不知道在哪，可以用whereis php尝试
#PHP_BIN=/usr/bin/php
#PHP_BIN=/opt/php5/bin/php
#PHP_BIN=php
PHP_BIN=/opt/php7/bin/php

#代码根目录
#SERVER_PATH=/www/bestdo/swooleTcpServer
SERVER_PATH=$(cd `dirname $0`; pwd)
SERVER_PATH=${SERVER_PATH%/*}


function start_application_server()
{

    filelist=$(ls $SERVER_PATH/Application)
    for applicationDir in $filelist
    do
        if [ -d $SERVER_PATH/Application/$applicationDir ]
        then
            serverDir=$(ls $SERVER_PATH/Application/$applicationDir)
            for serverfile in $serverDir
                do
                if [ -f $SERVER_PATH/Application/$applicationDir/$serverfile ] && [ `echo $serverfile | grep server|grep php` ]
                    then
                    echo Application/$applicationDir/$serverfile 'starting'
                    /opt/php7/bin/php $SERVER_PATH/Application/$applicationDir/$serverfile
                fi
            done
        fi
    done
}

getMasterPid()
{
    PID=`/bin/ps axu|grep swooleServer|grep swoole|grep master|awk '{print $2}'`
    echo $PID
}

getManagerPid()
{
    MID=`/bin/ps axu|grep swooleServer|grep swoole|grep manager|awk '{print $2}'`
    echo $MID
}

case "$1" in
        start)
                PID=`getMasterPid`
                if [ -n "$PID" ]; then
                    echo "server is running"
                    exit 1
                fi
                echo  "starting server "
                #$PHP_BIN $SERVER_PATH/Application/*/server.php
                start_application_server
                echo "done"
        ;;

        stop)
                PID=`getMasterPid`

                if [ -z "$PID" ]; then
                    echo "server is not running"
                    exit 1
                fi
                echo "shutting down server "

                #kill -9 $PID #杀掉master进程
                #kill $PID #杀掉master进程

                #MID=`getManagerPid`
                #kill -9 $MID #杀掉管理主进程
                echo "done"

                ps -ef | grep swooleServer|grep -v grep|awk '{print $2}'|xargs kill -9
        ;;

        status)
                PID=`getMasterPid`
                if [ -n "$PID" ]; then
                    echo "server is running"
                else
                    echo "server is not running"
                fi
        ;;

        force-quit)
                $0 stop
        ;;

        restart)
                $0 stop
                $0 start
        ;;

        reloadworker)
                MID=`getManagerPid`
                if [ -z "$MID" ]; then
                    echo  "server is not running"
                    exit 1
                fi
                echo  "reload worker server "
                #kill -USR1 $MID
                ps -ef | grep swooleServer|grep manager|grep -v grep|awk '{print $2}'|xargs kill -USR1
                echo "done"
        ;;

        reloadtask)
                MID=`getManagerPid`
                if [ -z "$MID" ]; then
                    echo  "server is not running"
                    exit 1
                fi
                echo  "reload task server"
                #kill -USR2 $MID
                ps -ef | grep swooleServer|grep manager|grep -v grep|awk '{print $2}'|xargs kill -USR2
                echo "done"
        ;;

        reload)
                $0 reloadworker
                $0 reloadtask
        ;;

        *)
                echo "Usage: $0 {start|stop|force-quit|restart|reload|reloadworker|reloadtask|status}"
                exit 1
        ;;

esac


