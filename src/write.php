<?php
declare(strict_types=1);

namespace icePHP;

function write(string $file, $content, int $flag = 0)
{
    //当前用户
    $current = getenv('USERNAME') ?: getenv('USER');

    //应该是这个用户
    $should = Config::get('system', 'OS_USER');

    //如果操作系统是Windows或当前已经是应该的用户,则不处理
    if (isWindows() or $current === $should) {
        return file_put_contents($file, $content, $flag);
    }

    //提前调整文件所有者
    if (function_exists('posix_getpwuid') && is_file($file)) {
        $owner = posix_getpwuid(fileowner($file));
        if ($owner !== $should) {
            chown($file, $should);
        }
    }

    //写入文件
    $ret = file_put_contents($file, $content, $flag);

    if (function_exists('posix_getpwuid')) {
        //延后调整文件所有者
        $owner = posix_getpwuid(fileowner($file));
        if ($owner !== $should) {
            chown($file, $should);
        }
    }

    return $ret;
}