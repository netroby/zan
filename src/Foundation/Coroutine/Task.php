<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/9
 * Time: 13:37
 */

namespace Zan\Framework\Foundation\Coroutine;

//load commands
Commands::load();

class Task {
    protected $taskId = 0;
    protected $parentId = 0;
    protected $coroutine = null;
    protected $context = null;

    protected $sendValue = null;
    protected $scheduler = null;
    protected $status = 0;

    public function __construct(\Generator $coroutine, $taskId=0, $parentId=0, Context $context=null) {
        $this->coroutine = $coroutine;
        $this->taskId = $taskId ? $taskId : TaskId::create();
        $this->parentId = $parentId;
        $this->context = $context;
        $this->scheduler = new Scheduler($this);
    }

    public function run(){
        while (true) {
            try {
                $this->status = $this->scheduler->schedule();
                switch($this->status) {
                    case Signal::TASK_KILLED:
                        return null;
                    case Signal::TASK_SLEEP:
                        return null;
                    case Signal::TASK_WAIT:
                        return null;
                    case Signal::TASK_DONE;
                        $this->fireTaskDoneEvent();
                        return null;
                }
            } catch (\Exception $e) {
                if($this->scheduler->isStackEmpty()) {
                    return ;
                }
                $this->scheduler->throwException($e);
            }
        }
    }

    public function getTaskId() {
        return $this->taskId;
    }

    public function getContext() {
        return $this->context;
    }

    public function setSendValue($sendValue) {
        $this->sendValue = $sendValue;
    }

    public function getSendValue() {
        return $this->sendValue;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($signal) {
        $this->status = $signal;
    }

    public function getCoroutine() {
        return $this->coroutine;
    }

    public function setCoroutine(\Generator $coroutine) {
        $this->coroutine = $coroutine;
    }

    public function fireTaskDoneEvent() {
        $evtName = 'task_event_' . $this->taskId;
        //$this->context->getEvent()->fire($evtName, $this->sendValue);
    }
}