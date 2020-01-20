<?php
namespace Bobby\MultiProcesses;

class InterProcessShareMemory
{
    protected $tempFile;

    protected $memoryResource;

    protected $autoRelease;

    public function __construct(string $memoryTokenKey, bool $autoRelease = true)
    {
        $this->autoRelease = $autoRelease;

        file_exists($this->tempFile = sprintf("%s/{$memoryTokenKey}", sys_get_temp_dir())) || touch($this->tempFile);

        $key = ftok($this->tempFile, $memoryTokenKey{0});
        if (!$this->memoryResource = shm_attach($key)) {
            throw new ProcessException("Create inter-process share memory fail.");
        }
    }

    public function set(int $key, $value)
    {
        return shm_put_var($this->memoryResource, $key, $value);
    }

    public function get(int $key)
    {
        return shm_get_var($this->memoryResource, $key);
    }

    public function delete(int $key)
    {
        return shm_remove_var($this->memoryResource, $key);
    }

    public function release()
    {
        // unlink($this->tempFile);
        // return shm_remove($this->memoryResource);
    }

    public function __destruct()
    {
        if ($this->autoRelease) {
            $this->release();
        }
    }
}