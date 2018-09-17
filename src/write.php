<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 对file_put_contents的封装,以修正文件所有者
 * @param $file string 文件名
 * @param $content string 文件内容
 * @param int $flag FILE_APPEND&|LOCK_EX
 */
function write(string $file, $content, int $flag = 0): void
{
    //当前用户
    $current = getenv('USERNAME') ?: getenv('USER');

    //应该是这个用户
    $should = configDefault($current,'system', 'OS_USER');

    //如果操作系统是Windows或当前已经是应该的用户,则不处理
    if (isWindows() or $current === $should) {
        if (false === file_put_contents($file, $content, $flag)) {
            trigger_error('写入文件失败:' . $file);
        };
        return;
    }

    //提前调整文件所有者
    if (function_exists('posix_getpwuid') && is_file($file)) {
        $owner = posix_getpwuid(fileowner($file));
        if ($owner !== $should) {
            chown($file, $should);
        }
    }

    //写入文件
    if (false === file_put_contents($file, $content, $flag)) {
        trigger_error('写入文件失败:' . $file);
    };

    if (function_exists('posix_getpwuid')) {
        //延后调整文件所有者
        $owner = posix_getpwuid(fileowner($file));
        if ($owner !== $should) {
            chown($file, $should);
        }
    }
}

/**
 * 越级创建目录
 * @param $path string 目录名称
 */
function makeDir(string $path): void
{
    //转换标准路径 分隔符
    $path = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $path);

    //如果已经是目录或文件
    if (is_dir($path) or is_file($path)) {
        return;
    }

    //上一级目录
    $parent = dirname($path);

    //如果上一级不是目录,则创建上一级
    if (!is_dir($parent)) {
        if (false === makeDir($parent)) {
            trigger_error('创建目录失败:' . $parent);
        };
    }

    //创建当前目录
    if (false === mkdir($path, 0777)) {
        trigger_error('创建目录失败:' . $path);
    };

    //Windows系统,不进行后续处理
    if (isWindows()) {
        return;
    }

    //当前用户
    $current = getenv('USERNAME') ?: getenv('USER');

    //应该是这个用户
    $should = configDefault($current,'system', 'OS_USER');

    //如果当前已经是应该的用户,则不处理
    if ($current === $should) {
        return;
    }

    //修改所有者为www(应该的用户)
    chown($path, $should);
}