<?php

namespace Lib\Infr;


class SecondRunLocker {

    private $_lockStatus = false;
    private $_lockFile = false;
    private $_optKey = 'default';    
    protected $_lastError = '';
    
    
    /**
     * Проверяет запущен ли процесс, если не запущен устанавливает блокировку
     *
     * @param string $lockFile - путь к файлу блокировки
     * @param string $optKey - ключ, идентификатор экземляра процесса
     * @return boolean
     */        
    
    public function lockProccess($lockFile, $optKey = false){
        
        if(!strlen($lockFile)){
            $this->_lastError = "Undefined .lock file";
            return false;
        }
        
        $this->_lockFile = $lockFile;
        
        if($optKey){
            $this->_optKey = $optKey;
        }
        
        if ($this->isLocked()) {
            $this->_lastError = "Task already run";
            return false;
        }
       
        //Если блокировка вдруг не поставилась
        if(!$this->setLock()){
            $this->_lastError = "Can't set lock ";
            return false;
        }
        return true;
    }

    public function getLastError() {
        return $this->_lastError;
    }
    
    
    /**
     * Проверяем файл блокировки и айди процесса     
     * @return boolean
     */
    
    private function isLocked(){
        $lockFile = $this->_lockFile."_".$this->_optKey;
        //$_optKey = $this->_optKey;

        if (file_exists($lockFile)) {
            
            $data = explode('|', file_get_contents($lockFile));
            $pid = $data[0];
            $key = $data[1];
    
            $ps=shell_exec("ps p".$pid);
            $ps=explode("\n", $ps);
    
            if(empty($ps[1])){
                //преген не запущен, работаем
                unlink($lockFile);
                return false;
            } else {
                if($key == $this->_optKey){
                    return true;
                }
            }
        }
        return false;
    }


     /**
      * Устанавливает блокировку
      * @return boolean
      * @throws \Exception
     */
        
    private function setLock(){
        
        // Это означает что скрипт запущен из под апача. Если сохранять PID то сохраниться PID апача, 
        // и скрипт больше не запуститься. Поэтому запуск из под апача не лочим.
        if (isset($_SERVER['HTTP_HOST']))
            return true;
        
        $lockFile = $this->_lockFile."_".$this->_optKey;	
        
        if(!file_put_contents($lockFile, getmypid()."|".$this->_optKey)){
            throw new \Exception("Can't create .lock file: ".$lockFile);

        } else {
            $this->_lockStatus = true;
            return true;
        }
    }

    
    /**
     * Снимает блокировку
     * @return boolean
     * @throws \Exception
    */    
    
    public function unsetLock() {
        // раз не лочим, то и не унлочим
        if (isset($_SERVER['HTTP_HOST']))
            return true;

        $lockFile = $this->_lockFile."_".$this->_optKey;
        
        if($lockFile){
            if(unlink($lockFile)){
                $this->_lockStatus = false;
                return true;
            } else
                throw new \Exception("Can't delete .lock file: ".$lockFile);
        }

        return true;
    }
    
    /**
     * чтобы не париться с доп.вызовами делаем снятие блокировки через __destruct
     *
     */
            
    function __destruct(){
        if ($this->_lockStatus) {
            $this->unsetLock();
        }
    }
}

