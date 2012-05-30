<?php
class CConsoleLogRoute extends CLogRoute
{
	protected function formatLogMessage($message,$level,$category,$time)
	{
		return @date('Y/m/d H:i:s',$time)."\t[$level]\t[$category]\t$message\n";
	}
    
	protected function processLogs($logs)
	{
        echo "\n*** LOGS ***\n";
        
		foreach($logs as $log)
		{
            echo $this->formatLogMessage($log[0],$log[1],$log[2],$log[3]);
        }
    }    
}

?>
