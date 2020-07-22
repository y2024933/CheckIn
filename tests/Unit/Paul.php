<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\account_record;
use App\Models\do_list;
use App\Models\do_list_audit;
use DB;

class Paul extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function testCheckIn()
    {
		$data      = [];
		$accountid = '2';
		$type      = '1';
		$now       = date("Y-m-d H:i:s");

		$data      = [
						'accountid' => $accountid,
						'type'      => $type,
						'status'    => '1',
						'datetime'  => $now,
        			];
        // $res = account_record::create($data);
        if(!empty($res->id)) {
        	// return ['code' => 0, 'msg' => 'success'];
        }
        // return ['code' => 1, 'msg' => 'failed'];
        $this->assertTrue(true);
    }

    public function testGetRecord()
    {
    	$starttime = date("Y-m-d 00:00:00");
    	$starttime = date("2000-m-d 00:00:00");
    	$endtime   = date("Y-m-d 23:59:59");
    	$accountid = ''; //空值就全查
    	$type      = ''; //空值就全查

        $res = account_record::selectRaw('accountid, DATE_FORMAT(date, "%Y-%m-%d") as d, type, status, date')
        					->whereBetween('date', [$starttime, $endtime])
        					->when($accountid, function($q, $accountid) {
        						$q->whereAccountid($accountid);
        					})
        					->when($type, function($q, $type) {
        						$q->whereType($type);
        					})
        					->orderBy('d', 'asc')
        					->get();
       	foreach ($res as $key => $val) {
       		if(empty($type) && empty($arr[$val['accountid']][$val['d']])) {
       			$arr[$val['accountid']][$val['d']]['1'] = '';
       			$arr[$val['accountid']][$val['d']]['2'] = '';
       		}
       		$arr[$val['accountid']][$val['d']][$val['type']] = $val['date'];
       	}
       	print_r($arr);
        $this->assertTrue(true);
    }

    public function testWriteList()
    {
		$data      = [];
		$data2     = [];
		$accountid = '4';
		$content   = 'test content';
		$now       = date("Y-m-d H:i:s");

    	$data      = [
						'accountid' => $accountid,
						'content'   => $content,
						'status'    => '1',
						'date'      => $now,
    				];
    	$check = do_list::whereAccountid($accountid)->get();
    	$method = $check->isEmpty() ? 'insert' : 'update';
    	DB::begintransaction();
    	// $res = do_list::updateOrCreate(['accountid' => $accountid], $data);
    	if(!empty($res->id)) {
    		$data2 = [
    					'mapping'    => $accountid,
						'method'     => $method,
						'para'       => json_encode($data),
						'updateuser' => $accountid,
						'date'       => $now
    		];
    		$res2 = do_list_audit::create($data2);
    		if(!empty($res2->id)) {
    			DB::commit();
    			// return ['code' => 0, 'msg' => 'success'];
    		}
    	}
    	DB::rollback();
    	// return ['code' => 1, 'msg' => 'failed'];
        $this->assertTrue(true);
    }

    public function testGetList()
    {
    	$res = do_list::whereStatus('1')->get();
    	if(!$res->isEmpty()) {
    		foreach ($res as $key => $val) {
    			$data[$val['accountid']] = $val['content'];
    		}
    	}
    	// print_r($data);
        $this->assertTrue(true);
    }

    public function testDelList()
    {
    	$mid = '2';
    	$accountid = '2';

    	$check = 0;
    	$res = do_list::whereAccountid($accountid)->get();
    	if(!$res->isEmpty()) {
    		DB::begintransaction();
    		// $check = do_list::whereAccountid($accountid)->update(['status' => '0']);
	    	if(!empty($check)) {
	    		$data = [
    						'mapping'    => $accountid,
	    					'method'     => 'update',
	    					'para'       => json_encode(['status' => '0']),
	    					'updateuser' => $mid,
	    					'date'       => date("Y-m-d H:i:s")
	    		];
	    		// $res2 = do_list_audit::create($data);
	    		if(!empty($res2->id)) {
	    			DB::commit();
		    		// return ['code' => 0, 'msg' => 'delete success'];
	    		}
	    	}
    		DB::rollback();
    	}
    	// return ['code' => 1, 'msg' => 'delete failed'];
        $this->assertTrue(true);
    }
}
