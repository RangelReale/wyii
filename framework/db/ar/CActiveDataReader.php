<?php
class CActiveDataReader extends CComponent implements Iterator, ArrayAccess
{
    private $_finder;
    private $_query;
    private $_command;
    private $_reader;
    private $_current = null;
	private $_position;
    
    public function __construct($finder,$query,$command)
    {
        $this->_finder = $finder;
        $this->_query = $query;
        $this->_command = $command;
        $this->_reader = $command->query();
		$this->_position = 0;
    }
    
    public function current()
    {
        if ($this->_current === null)
        {
            $this->populateCurrent();
        }
        return $this->_current;
    }
    
    public function key()
    {
        return $this->_position;
    }
    
    public function next()
    {
        $this->_reader->next();
		$this->_position++;
        $this->_current = null;
    }
    
    public function rewind()
    {
        $this->_reader->rewind();
		$this->_position = 0;
        $this->_current = null;
    }
    
    public function valid()    
    {
        return $this->_reader->valid();
    }
    
    
    private function populateCurrent()
    {
		if ($this->_finder instanceof CActiveRecord)
			$this->_current = $this->_finder->populateRecord($this->_reader->current(), true);
		else
		{
			$this->_current = $this->_finder->populateRecord($this->_query, $this->_reader->current());
			$this->_current->afterFindInternal();
		}
    }
    
    // forge primary key checking
    public function offsetSet($offset, $value) {
        // do nothing
    }
    public function offsetExists($offset) {
        //return isset($this->_keys[$offset]);
        return false;
    }
    public function offsetUnset($offset) {
        //unset($this->_keys[$offset]);
    }
    public function offsetGet($offset) {
        return null;
    }    
}
?>
