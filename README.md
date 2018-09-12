处理Linux下的写权限和用户问题
=

* 写文件

    write(string $file, $content, int $flag = 0)

    对file_put_contents的封装,以修正文件所有者

* 创建目录

    makeDir(string $path): void
    
    可以越级创建目录

#### 写入失败时会触发错误，而不是异常