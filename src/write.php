<?php
declare(strict_types=1);

namespace icePHP;

/**
 * 对file_put_contents的封装,初始化文件所有者(解决root执行www用户无法写入问题)
 * @param $file string 文件名
 * @param $content string 文件内容
 * @param int $flag FILE_APPEND&|LOCK_EX
 */
function write(string $file, $content, int $flag = 0): void
{
    //判断文件是否存在
    $isExist = is_file($file);
    
    //已经存在 或 操作系统是Windows 直接写入
    if (isWindows() || $isExist) {
        if (false === file_put_contents($file, $content, $flag)) {
            trigger_error('写入文件失败:' . $file);
        };
        return;
    }

    //如果是linux系统并且不存在 第一次写入并指定所有者
    if (false === file_put_contents($file, $content, $flag)) {
        trigger_error('写入文件失败:' . $file);
    };
    
    //默认缺省值为www用户
    $defaultUser = 'www';
    
    //被指定的用户
    $user = configDefault($defaultUser,'system', 'OS_USER');
    
    chown($file, $user);
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