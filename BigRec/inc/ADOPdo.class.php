<?php
/**
 * 2016-12-02 更新Autoalter无法自动更新字段功能
 * 2017-03-17 支持持久化常连接
 * 2017-08-17 修正 持久化连接问题
对象：ADOPdo
本对象使用PHP PDO 接口，实现数据库的一些操作

Debug()			临时输出一句语句

GetOne			输出一行数据中的某个字段
(SQL strint)

GetRow			输出一行数据的所有字段为数组
(SQL strint)

GetAll			输出所有数据为数组
(SQL strint)

SelectLimit		输出选中的行数据为数组
(SQL strint,Size,Limit)

InsertID		输出最近添加的一条语句的自增ID

AutoExecute		自动选择添加修改方式
(BaseName,Data,INSERT|UPDATE,[@UPDATE WHERE])
*/

class ADOPdo{
    private  $ADODBaseConnectID     = null;
    private  $ADOCBaseName			= "mysql";
    private  $ADOCBaseErrot;
    private  $JoinInsertID			= 0;
    private  $DeBug					= false;
    private  $End                    = "<br>\r\n";
	
    private static $StaticAdodbObject = null;
    
    public static function Start($Host='',$User='',$Pass='',$Name='',$OpenType=true){
    	if( self::$StaticAdodbObject==null && $Host!="" && $User!="" && $Pass!="" ){
    		self::$StaticAdodbObject = new ADOPdo("mysql");
    		self::$StaticAdodbObject->Connect($Host, $User, $Pass, $Name,$OpenType);
    	}
    	
    	if( is_object(self::$StaticAdodbObject) )return self::$StaticAdodbObject;
    	if( self::$StaticAdodbObject==null )return false;
    }
	
    

    //////////////////////////////////////////////////////////
    public function __construct($BaseType){
        $this->ADOCBaseName = $BaseType;
    }




    //////////////////////////////////////////////////////////
    public function Connect($http,$user,$pass,$name='',$longopen=true){
    	
    	if( $longopen==true ){
    		$open	= array(PDO::ATTR_PERSISTENT => true);
    	}else{
    		$open	= array();
    	}
    	
        try{
            $db                         = new PDO("{$this->ADOCBaseName}:host={$http};dbname={$name}", $user, $pass, $open); //初始化一个PDO对象
            $this->ADODBaseConnectID    = $db;
            $this->Execute("SET NAMES 'utf8'");
        }catch (PDOException $e) {
            die ("Error!: " . $e->getMessage() . "<br/>");
            die('Connection mysql error!');
        }
        
        return true;
    }



    //选择数据库
    public function Selectdb($dbName=""){
        if( $dbName!=""  ){
            $sc     = $this->ADODBaseConnectID->query("USE {$dbName}");
            if( !$sc ){
                $this->ADOCBaseErrot = "无法连接数据表，请检查数据表{$dbName}是否存在！";
                return false;
            }else{
                return true;
            }
        }
    }



    //取唯一记录
    public function GetOne($SQL){
        $Row    = $this->GetRow($SQL);
        return ( empty($Row) )? 0 : current($Row);
    }
	
    
    //取单笔记录
    public function GetRow($SQL){
        if(	false!==$Relsut=$this->ADODBaseConnectID->query($SQL) ){
            $Row=$Relsut->fetch(PDO::FETCH_ASSOC);
            $this->SaveDeBug($SQL,$Row,true);
            return $Row;
        }else{
            $this->SaveDeBug($SQL,$Row,false);
            return array();
        }
    }
    
    
    

    //取得所有记录
    public function GetAll($SQL){
        
        if( preg_match('/^select /i',$SQL) && !preg_match('/ limit [0-9]+[0-9,]*/i', $SQL) ){
            $SQL       .= " LIMIT 0,100000";
        }
        $Arr            = array();
        if(	false!==$Relsut=$this->ADODBaseConnectID->query($SQL) ){
            while( is_array($Rs=$Relsut->fetch(PDO::FETCH_ASSOC)) ){
                $Arr[]  = $Rs;
            }
            $this->SaveDeBug($SQL,$Arr,true);
            return $Arr;
        }else{
            $this->SaveDeBug($SQL,$Arr,false);
            return array();
        }
    }
    
    
    

    //取得记录条数
    public function SelectLimit($SQL,$Size,$Lim=0){
        $SQL .= " limit $Lim,$Size";
        return $this->GetAll($SQL);
    }
    
    
	
    
    //execute
    public function Execute($SQL){
        $ExInt  = $this->ADODBaseConnectID->exec($SQL);
        $NewId  = $this->ADODBaseConnectID->lastInsertId();
        
        if( ceil($NewId)>0 ){
            $this->JoinInsertID = $NewId;
        }
        
        if( ceil($ExInt)<=0 ){
            $this->SaveDeBug($SQL,array(),false);
            return false;
        }else{
            $this->SaveDeBug($SQL,$ExInt,true);
            return true;
        }
    }






    /**
     * 自动执行数据写入动作
     * @param 需执行的库名 $Base
     * @param 需执行的数据 $Arr
     * @param 执行方法INSERT|UPDATE|DELETE $Type
     * @param 执行条件 $Where
     */
    public function AutoExecute($Base,$Arr,$Type,$Where=''){

        if(strtoupper($Type)=="INSERT"){
            return $this->InsertArr($Base,$Arr);
        }
        
        if(strtoupper($Type)=="UPDATE"){
            return $this->UpdateArr($Base,$Where,$Arr);
        }
        
        if( strtoupper($Type)=="DELETE" ){
        	$Where		= ( empty($Where) )? "" : " WHERE {$Where}";
        	return $this->Execute("DELETE  FROM {$Base} {$Where}");
        }
        
        return false;
    }
    
    
    
    /**
     * 获取最后一条插入的序号
     */
    public function InsertID(){
    	if( ceil($this->JoinInsertID)>0 ){
    		return $this->JoinInsertID;
    	}else{
    		return 0;
    	}
    }
    
    
    
    
    /**
     * 根据需要写入的数据自动创建字段
     * @param 需要写入的表名 $TbName
     * @param 需要写入的数据 $Row
     */
    protected static $parse_alert   = array();
    public function AutoAlter($TbName,$Row ){
    	//如果写入数据为空则直接反真
    	if( empty($Row) )return true;
    	 
    	//如果发现已经被执行过的表,则跳过
    	if( in_array($TbName,self::$parse_alert) ) return true;
    	
    	$TabArr     = $this->GetAll("desc {$TbName}");
    	
    	//如果表结构不存在，则表示该表尚未创建
    	if( empty($TabArr) ){
    		
    		$SQL		= "CREATE TABLE `{$TbName}` (`createtime` timestamp NULL DEFAULT NULL) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='';";
    		
    		reset($Row);
    		$RowKey		= array_keys($Row);
    		if( preg_match('/_id/i',$RowKey[0]) && preg_match('/^[0-9]{1,11}$/i', current($Row)) ){
    			$SQL		= "CREATE TABLE `{$TbName}` (`{$RowKey[0]}` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增序号',PRIMARY KEY (`{$RowKey[0]}`)) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='';";
    		}
    		
    		$this->Execute($SQL);
    		$TabArr     = $this->GetAll("desc {$TbName}");
    		//创建失败
    		if( empty($TabArr) ){
    			$this->Err	= "创建新表失败";
    			return false;
    		}
    	}
    	
    	$Desc           = array();
    	foreach($TabArr as $Rs){
    		$Desc[]     = $Rs['Field'];
    	}
    	
    	$Alert          = array();
    	
    	//开始根据需要写入的数据生成需要运行的语句
    	foreach($Row as $RsName=>$RsVal){
    		
    		$RsName     = strtolower($RsName);
    		
    		//如果字段已经存在,则跳过
    		if( in_array($RsName, $Desc) )continue;
    		
    		//如果是日期格式
    		if( preg_match('/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2}$/i', $RsVal) ){
    			$Alert[]        = "ALTER TABLE {$TbName} ADD COLUMN {$RsName} date DEFAULT NULL;";
    		}
    		
    		//如果是时间格式
    		if( preg_match('/^[0-9]{4,4}-[0-9]{2,2}-[0-9]{2,2} [0-9]{2,2}:[0-9]{2,2}:[0-9]{2,2}$/i', $RsVal) ){
    			$Alert[]        = "ALTER TABLE {$TbName} ADD COLUMN {$RsName} datetime DEFAULT NULL;";
    		}
    		
    		//如果不超过11位,并且均为数字,则定义格式为数字
    		if( preg_match('/^[0-9]{1,11}$/i', $RsVal) ){
    			$Alert[]        = "ALTER TABLE {$TbName} ADD COLUMN {$RsName} int(11) DEFAULT '0';";
    		}
    		
    		//如果长度不超过255,则执行为varchar
    		if( !preg_match('/^[0-9]{1,11}$/i', $RsVal)&&strlen($RsVal)<255 ){
    			$Alert[]        = "ALTER TABLE {$TbName} ADD {$RsName} varchar(255) DEFAULT NULL;";
    		}
    		
    		//如果长度超过255,则定义为text
    		if( strlen($RsVal)>=255 ){
    			$Alert[]        = "ALTER TABLE {$TbName} ADD {$RsName} TEXT DEFAULT NULL;";
    		}
    		
    		//如果设定的是自增序号
    		if( $RsVal=='_autoid' ){
    			$Alert[]        = "ALTER TABLE {$TbName} ADD `{$RsName}` int(12) NOT NULL AUTO_INCREMENT COMMENT '';";
    		}
    	}
    	
    	//开始执行数据结构的修正
    	if( !empty($Alert) ){
    		foreach($Alert as $SQL){
    			$this->Execute($SQL);
    		}
    	}
    	self::$parse_alert[]    = $TbName;
    	return true;
    }
    
    
    
    /**
     * 开启下一条SQL语句输出
     */
    public function DeBug(){
    	$this->DeBug		= true;
    }
    
    
    /**
     * 返回数据库错误
     * @return string
     */
    public function GetErr(){
    	return '';
    }
    
    
    
    //可以是一维数组，也可以是二维数组，但是当为二维数组的时候请将数据库字段名在二维数组中
    protected function InsertArr($BaseName,$Array=array()){
    	$IN_NAME	= array();
    	$IN_VALUE	= array();
    	$IN_ARR		= 0;
    	if( count($Array)>0 ){
    		foreach($Array as $As=>$Vs){
    			if( is_array($Vs) ){
    				$IN_ARR		= 1;
    				$TM_VALUE	= array();
    				$IN_NAME	= array();
    				foreach($Vs as $ks=>$vs){
    					$IN_NAME[]	= "`{$ks}`";
    					$TM_VALUE[] = "'{$vs}'";
    				}
    				$IN_VALUE[] = "(".implode(',',$TM_VALUE).")";
    			}else{
    				$IN_NAME[]	= "`{$As}`";
    				$IN_VALUE[] = "'{$Vs}'";
    			}
    		}
    		$IN_NAME	= implode(',',$IN_NAME);
    		if( $IN_ARR==0 ){
    			$IN_VALUE = "(".implode(',',$IN_VALUE).")";
    		}else{
    			$IN_VALUE = implode(',',$IN_VALUE);
    		}
    
    
    		$IN_SQL		= "INSERT INTO {$BaseName} ({$IN_NAME}) VALUE {$IN_VALUE}";
    		$IN_EXE		= $this->Execute($IN_SQL);
    
    		return $IN_EXE;
    	}
    
    	return false;
    }
    
    //修改记录的数组方式,仅支持一维数组
    protected function UpdateArr($BaseName,$BaseWHERE,$Array=array()){
    	$IN_VALUE	= array();
    
    	if( count($Array)>0 && is_array($Array) ){
    		foreach($Array as $As=>$Vs){
    			$IN_VALUE[]	= "`{$As}`='{$Vs}'";
    		}
    
    		$IN_VALUE	= implode(",",$IN_VALUE);
    		$UPSQL		= "UPDATE {$BaseName} SET {$IN_VALUE} WHERE $BaseWHERE";//echo $UPSQL;//die;
    		$UPEXE		= $this->Execute($UPSQL);
    		return $UPEXE;
    	}
    
    	return false;
    }
    
    
    //隐藏调试的时候插入数据和结果
    protected function SaveDeBug($SQL,$DATA,$STATUS){
        
        if( $this->DeBug ){
        	echo "<pre>";
            echo $SQL.$this->End;
            echo "</pre>";
            $this->DeBug	= false;
        }
        
    }
}
