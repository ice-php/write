# 处理Linux下的写权限和用户问题

## 对file_put_contents的封装,以修正文件所有者
write(string $file, $content, int $flag = 0)

## 越级创建目录
makeDir(string $path): void

