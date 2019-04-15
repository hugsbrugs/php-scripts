<?php

namespace Hug\Scripts;

use Exception;

use Hug\FileSystem\FileSystem as FileSystem;

/**
 * PHP Scripts Utilities functions
 */
class Scripts
{

    /**
     * Launch a script with it's optional dedicated log file
     *
     * @param string $cmd shell command line to run 
     * @param string $log_file path to specified log file where to write script output
     *
     * @return array $result (status / message / data)
     *
     * @link http://stackoverflow.com/questions/1019867/is-there-a-way-to-use-shell-exec-without-waiting-for-the-command-to-complete
     */
    public static function run($cmd, $log_file = null)
    {
        $result = ['status' => 'error', 'message' => '', 'data' => null];

        try
        {
            # Create tmp file to store process PID
            $tmp_pid_file = tempnam(sys_get_temp_dir(), 'tmp_pid_file_');

            if($log_file!==null)
            {            
                # Create dir structure if not existing
                $write = FileSystem::force_file_put_contents($log_file, '');
                if($write['status']==='error')
                {
                    throw new Exception("Error Creating Log File " . $log_file, 1);
                }

                # Launch command
                exec(sprintf("(%s) > %s 2>&1 & echo $! >> %s", $cmd, $log_file, $tmp_pid_file));
            }
            else
            {
                # Launch command
                exec(sprintf("(%s) > /dev/null 2>&1 & echo $! >> %s", $cmd, $tmp_pid_file));
                //exec(sprintf("%s 2>&1 & echo $! >> %s", $cmd, $tmp_pid_file));
            }

            $pid = null;

            # Retrieve PID
            if(file_exists($tmp_pid_file))
            {
                $pid = trim(file_get_contents($tmp_pid_file));
            }

            # Delete $tmp_pid_file (was just used to retrieve PIP)
            unlink($tmp_pid_file);

            $result['status'] = 'success';
            $result['data'] = [
                'pid' => $pid, 
                'log' => $log_file
            ];

        }
        catch(Exception $e)
        {
            $result['message'] = 'Scripts::run : ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Kills a running script (tail log files)
     *
     * @param int $pid PID to kill
     * @return bool $killed
     */
    public static function kill($pid)
    {
        $killed = false;

        try
        {
            if(Scripts::is_running($pid))
            {
                if (!defined('SIGTERM')) {
                    define('SIGTERM',9);
                }
                $killed = posix_kill($pid , SIGTERM);
                // $killed = posix_kill($pid , SIGKILL);
            }
        }
        catch(Exception $e)
        {
            error_log("Scripts::kill_script PID :" . $pid . ' Error : ' . $e->getMessage());
        }
        return $killed;
    }

    /**
     * Tells if a process is running or not
     * 
     * @param string $pid process PID
     * @return bool $is_running
     *
     */
    public static function is_running($pid)
    {
        $is_running = true;

        # posix_getpgid will return false when a process is not running
        if(is_integer((int)$pid))
        {
            if(!posix_getpgid((int)$pid))
            {
                $is_running = false;
            }
        }
        else
        {
            $is_running = false;
        }
        
        return $is_running;
    }


    /**
     * Returns an array with cpu / mem usage
     *
     * @param string $pid process pid
     * @return array $stats
     *
     */
    public static function get_pid_cpu_mem($pid)
    {
        $stats = ['mem' => 0, 'cpu' => 0];
        
        if(Scripts::is_running($pid))
        {
            $cpu_mem = shell_exec('ps -p ' . $pid . ' -o "pcpu,pmem" | tail -n +2');
            # array_values re-index array [0] [1]
            # array_filter removes array empty rows
            $res = array_values( array_filter(explode(' ', $cpu_mem)) );
            $stats['cpu'] = trim($res[0]);
            $stats['mem'] = trim($res[1]);
        }

        return $stats;
    }


    /**
     * Returns uid who owns pid
     *
     * @param string $uid
     * @return string $uid
     */
    /*public static function get_pid_uid($pid)
    {
        $uid = null;
        
        # Retourne l'id du groupe de processus
        $pgid = posix_getpgid($pid);
        // error_log('pgid : ' . $pgid);
        # Retourne le sid du processus (groupe du processus du gestionnaire de session)
        $sid = posix_getsid($pid);
        // error_log('sid : ' . $sid);

        return $uid;
    }*/

    /**
     * Execute the given command by displaying console output live to the user. Helper function to debug in browser
     *
     * @param string $cmd command to be executed
     * @return array exit_status: exit status of the executed command, output: console output of the executed command
     * @link http://stackoverflow.com/questions/20107147/php-reading-shell-exec-live-output
     */
    public static function run_live($cmd)
    {
        while (@ ob_end_flush()); // end all output buffers if any

        # Run command
        $proc = popen("$cmd 2>&1 ; echo Exit status : $?", 'r');

        $live_output     = "";
        $complete_output = "";

        while (!feof($proc))
        {
            $live_output = fread($proc, 4096);
            $complete_output = $complete_output . $live_output;
            echo "$live_output";
            @ flush();
        }

        pclose($proc);

        # get exit status
        preg_match('/[0-9]+$/', $complete_output, $matches);

        # Return exit status and intended output
        return [
            'exit_status' => $matches[0],
            'output' => str_replace("Exit status : " . $matches[0], '', $complete_output)
        ];
    }
}
