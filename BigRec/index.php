<?php
require_once 'inc/auto.inc.php';

$Action				= strtolower(trim($_GET['act']));
$Json				= array();
$Ado				= ADOPdo::Start();
$NoPreg				= '/^[a-z0-9-_]{1,50}$/i';
$NoUserPreg			= '/^u-[a-z0-9]{30,30}$/i';

// $EchoSize		= ob_get_length();
// header("Content-Length: {$EchoSize}");  //告诉浏览器数据长度,浏览器接收到此长度数据后就不再接收数据
// header("Connection: Close");      		//告诉浏览器关闭当前连接,即为短连接
// ob_flush();
// flush();

SyncApp();


//待选池 与 选择池的倍数,默认100倍待选然后随机。
$loadMaxPer				= 10;
//冷启动的 启用标准,以申报记录为准数字
$loadStartZero			= 100;
//多样性保持基数：当用户获取条数低于该条数时，用户多样性将失效，请注意设置阀值,如果设置为0则关闭该功能
//多样性：为了防止用户一段时间关注内容过于集中，所以当用户获取条数高于阀值，则会在待选池中加入10%的用户关注之外的最热门标签内容，最终选择池出现几率不定。
//注意，该功能在冷启动下无效
$loadNonUniqueMini		= 5;



//待选池		待选的ID池
$AppIdElected			= array();
//选择池     	已经选择的ID池
$AppIdChoice			= array();
//选择池所需记录总数
$loadSize				= 10;
//待选池所需记录总数
$loadMax				= $loadSize * $loadMaxPer;




switch($Action){
	/**
	 * 1.1	应用创建接口
	 */
	case "app_create":
		$Arr					= LoadData('app_name,app_exp,app_password,app_key');
		
		if( empty($Arr['app_name'])||empty($Arr['app_exp'])||empty($Arr['app_password'])||empty($Arr['app_key']) ){
			$Json['status']		= false;
			$Json['error']		= '关键数据缺失';
			break;
		}
		
		$AppOnly				= $Ado->GetOne("SELECT count(app_id) FROM big_app WHERE app_name='{$Arr['app_name']}'");
		if( ceil($AppOnly)>0 ){
			$Json['status']		= false;
			$Json['error']		= '重复的应用名称';
			break;
		}
		
		$Arr['app_reco_data']	= 7;
		$Arr['app_time_create']	= date("Y-m-d H:i:s");
		
		if( isset($Arr['app_password'])&& ( empty($Arr['app_password'])||strlen($Arr['app_password'])<6 ) ){
			$Json['status']			= false;
			$Json['error']			= '应用管理密码不能为空且不能少于6位';
			break;
		}
		
		if( isset($Arr['app_key'])&& ( empty($Arr['app_key'])||strlen($Arr['app_key'])<6 ) ){
			$Json['status']			= false;
			$Json['error']			= '应用提交密钥不能为空且不能少于6位';
			break;
		}
		
		$Ex						= $Ado->AutoExecute('big_app', $Arr, 'INSERT');
		$AppID					= $Ado->InsertID();
		
		if( !$Ex || empty($AppID) ){
			$Json['status']		= false;
			$Json['error']		= '创建失败';
		}else{
			$Json['status']		= true;
			$Json['error']		= '';
			$Json['app_id']		= $AppID;
			SyncApp($AppID);
		}
		
	break;
	
	
	
	
	/**
	 * 1.2	应用修改接口
	 */
	case "app_modify":
		$Arr					= LoadData('app_id,app_pass,app_name,app_exp,app_password,app_key,app_reco_data');
		
		if( empty($Arr['app_id'])||empty($Arr['app_key']) ){
			$Json['status']		= false;
			$Json['error']		= '关键数据缺失';
			break;
		}
		
		$AppRow					= $Ado->GetRow("SELECT * FROM big_app WHERE app_id='{$Arr['app_id']}'");
		if( empty($AppRow) ){
			$Json['status']		= false;
			$Json['error']		= '不存在的数据';
			break;
		}
		
		if( md5($Arr['app_pass'])!=md5($AppRow['app_password']) ){
			$Json['status']		= false;
			$Json['error']		= '管理密码错误';
			break;
		}
		
		unset($Arr['app_pass']);
		
		if( isset($Arr['app_name']) && $Arr['app_name']!=$AppRow['app_name'] ){
			if( empty($Arr['app_name']) ){
				$Json['status']		= false;
				$Json['error']		= '应用名称重复';
				break;
			}
			
			$AppOnly				= $Ado->GetOne("SELECT count(app_id) FROM big_app WHERE app_name='{$Arr['app_name']}'");
			if( ceil($AppOnly)>0 ){
				$Json['status']		= false;
				$Json['error']		= '应用名称重复';
				break;
			}
		}
		
		if( isset($Arr['app_exp']) && empty($Arr['app_exp']) ){
			$Json['status']			= false;
			$Json['error']			= '应用简介不能为空';
			break;
		}
		
		if( isset($Arr['app_password']) && ( empty($Arr['app_password'])||strlen($Arr['app_password'])<6 ) ){
			$Json['status']			= false;
			$Json['error']			= '应用管理密码不能为空且不能少于6位';
			break;
		}
		
		if( isset($Arr['app_key'])&& ( empty($Arr['app_key'])||strlen($Arr['app_key'])<6 ) ){
			$Json['status']			= false;
			$Json['error']			= '应用提交密钥不能为空且不能少于6位';
			break;
		}
		
		if( ceil($Arr['app_reco_data'])<1||ceil($Arr['app_reco_data'])>90 ){
			$Json['status']			= false;
			$Json['error']			= '统计周期天数只能大于1且小于90天';
			break;
		}
		
		$Ex						= $Ado->AutoExecute('big_app', $Arr, 'UPDATE', "app_id='{$Arr['app_id']}'");
		if( !$Ex ){
			$Json['status']		= false;
			$Json['error']		= '修改失败,数据未发生任何改变';
		}else{
			$Json['status']		= true;
			$Json['error']		= '';
			SyncApp($Arr['app_id']);
		}
	break;
	
	
	
	
	
	
	/**
	 * 1.3	用户资料添加|修改接口
	 */
	case "user_modify":
		$Arr					= LoadData('app_id,app_password,user_no,user_sex,user_age,user_phone');
		
		$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		unset($Arr['app_password']);
		$Arr['user_time_create']	= date("Y-m-d H:i:s");
		
		if( !preg_match($NoPreg, $Arr['user_no']) ){
			$Json['status']		= false;
			$Json['error']		= '用户序号非法,仅支持字母、数字、下横线、点（英文）等符号，最长32位';
			break;
		}
		
		if( isset($Arr['user_sex'])&&$Arr['user_sex']!='男'&&$Arr['user_sex']!='女'&&$Arr['user_sex']!='保密' ){
			$Json['status']		= false;
			$Json['error']		= '用户性别错误';
			break;
		}
		
		if( isset($Arr['user_age'])&&ceil($Arr['user_age'])>200&&ceil($Arr['user_age'])<1 ){
			$Json['status']		= false;
			$Json['error']		= '用户年龄错误';
			break;
		}
		
		if( isset($Arr['user_phone'])&&!preg_match('/^[0-9\+]{11,15}$/i', $Arr['user_phone']) ){
			$Json['status']		= false;
			$Json['error']		= '用户手机号码错误';
			break;
		}
		
		$Row					= ParseArray($Arr);
		$RowName				= implode(",", $Row['name']);
		$RowValue				= implode(",", $Row['value']);
		$RowSet					= implode(",", $Row['set']);
		$Ex						= $Ado->Execute("INSERT INTO big_user ({$RowName}) VALUES({$RowValue}) ON DUPLICATE KEY UPDATE {$RowSet}");
		
		if( $Ex ){
			$Json['status']		= true;
			$Json['error']		= '';
			break;
		}else{
			$Json['status']		= false;
			$Json['error']		= '写入失败';
			break;
		}
		
	break;
	
	
	
	
	
	/**
	 * 1.4	用户资料移除接口
	 */
	case "user_remove":
		$Arr					= LoadData('app_id,app_password,user_no');
		
		$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		
		$Ado->Execute("DELETE FROM big_user WHERE user_no={$Arr['user_no']} AND app_id={$Arr['app_id']}");
		
		$Json['status']			= true;
		$Json['error']			= '';
		break;
		
	break;
	
	
	
	
	
	
	/**
	 * 1.5	统计资料添加|修改接口
	 */
	case "data_modify":
		
		$Arr					= LoadData('app_id,app_password,val_no,val_name,val_cover,val_tags,val_show,val_grade');
		$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		unset($Arr['app_password']);
		
		if( isset($Arr['val_grade'])&&!empty($Arr['val_grade'])&&(ceil($Arr['val_grade'])<0||ceil($Arr['val_grade'])>9 ) ){
			$Json['status']		= false;
			$Json['error']		= '显示权重数据非法';
			break;
		}
		
		//添加标签资料
		$TagArr					= explode(",", $Arr['val_tags']);
		if( empty($TagArr) ){
			$Json['status']		= false;
			$Json['error']		= '资料必须要有标签';
			break;
		}else{
			$New				= array();
			foreach($TagArr as $Rs){
				$New			= array('app_id'=>$Arr['app_id'],'app_tags'=>$Rs);
				$Ado->AutoExecute('big_app_tags', $New, 'INSERT');
			}
		}
		
		$Arr['val_time_update']	= date("Y-m-d H:i:s");
		
		$Row					= ParseArray($Arr);
		$RowName				= implode(",", $Row['name']);
		$RowValue				= implode(",", $Row['value']);
		$RowSet					= implode(",", $Row['set']);
		$Ex						= $Ado->Execute("INSERT INTO big_value ({$RowName}) VALUES({$RowValue}) ON DUPLICATE KEY UPDATE {$RowSet}");
		
		if( $Ex ){
			$Json['status']		= true;
			$Json['error']		= '';
			break;
		}else{
			$Json['status']		= false;
			$Json['error']		= '写入失败';
			break;
		}
		
	break;
	
	
	
	
	
	/**
	 * 1.6	统计资料移除接口
	 */
	case "data_remove":
		$Arr					= LoadData('app_id,app_password,val_no');
		
		$ExistsApp				= ExistsApp($Arr['app_id'], $Arr['app_password']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		unset($Arr['app_password']);
		
		$Ado->Execute("DELETE FROM big_value WHERE val_no={$Arr['val_no']} AND app_id={$Arr['app_id']}");
		
		$Json['status']			= true;
		$Json['error']			= '';
		break;
	break;
	
	
	
	
	
	/**
	 * 2.1	自动推荐接口
	 */
	case "reco":
		$Arr					= LoadData('app_id,app_key,user_no,size','get');
		$Cok					= LoadData('bigrec_userno','cookie');
		
		$ExistsApp				= ExistsApp($Arr['app_id'],null,$Arr['app_key']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		
		unset($Arr['app_key']);
		
		//选择池所需记录总数
		$loadSize				= ( preg_match('/^[0-9]+$/i',$Arr['size'])&&ceil($Arr['size'])>1&&ceil($Arr['size'])<101)? ceil($Arr['size']) : 10;
		//待选池所需记录总数
		$loadMax				= $loadSize * $loadMaxPer;
		
		$AppRow					= $Ado->GetRow("SELECT * FROM big_app_cache WHERE app_id={$Arr['app_id']}");
		
		
		//用户当前信息
		$User					= array();
		$User['user_ip']		= ip2long( FunGetTrueIP() );
		$User['user_ip']		= ( !preg_match('/^[0-9]+$/',$User['user_ip']) )? 0 : $User['user_ip'];
		$User['user_brower']	= FunGetBrowse();
		$User['user_os']		= FunGetOs();
		
		//设定被静态获取的数据条数
		$Json['info']['static']		= 0;
		//设定被差额补充的条数
		$Json['info']['supplement']	= 0;
		//待选池总数
		$Json['info']['elected']	= 0;
		//通过选择方法获取到的数据条数
		$Json['info']['choices']	= 0;
		//设定数据选择的方式方法
		$Json['info']['choice'] 	= array();
		//待选池出现过的标签
		$ElectedTags				= array();
		
		
		
		
		//如果没有传输用户序号,并且没有COOKIE用户标识,则强制写入游客序号
		if( !preg_match($NoPreg, $Arr['user_no']) && !preg_match($NoUserPreg, $Cok['bigrec_userno']) ){
			$UserNo				= 'u-'.FunRandABC(30);
			setcookie('bigrec_userno',$UserNo,time()+86400*5);
		}else{
			if( preg_match($NoPreg, $Arr['user_no']) ){
				$UserNo			= $Arr['user_no'];
			}else{
				$UserNo			= $Cok['bigrec_userno'];
			}
		}
		
		
		//固定搜索权限为9的数据
		$TopChoice							= $Ado->SelectLimit("SELECT * FROM big_value WHERE app_id={$Arr['app_id']} AND val_show='true' AND val_grade=9 ORDER BY val_time_update DESC",$loadSize);
		if( !empty($TopChoice) ){
			$loadSize						= ceil( $loadSize - count($TopChoice) );
			$loadSize						= ( $loadSize>0 )? $loadSize : 0;
			$loadMax						= $loadSize * $loadMaxPer;
		}
		$Json['info']['static']				= count($TopChoice);
		
		
		/**
		 * 冷启动,不管是用户还是游客
		 * 算法:首先根据标签池获取当前应用所有标签,然后根据标签个数/待选池记录总数=平均标签记录数,然后根据显示权重倒叙获取每个标签记录数量
		 * 最后，如果获取记录总数不等于 待选池所需总数,则以资料更新时间倒叙获取 差数，填充直到满足  待选池所需总数
		 */
		if( ceil($AppRow['app_total_declaration'])<$loadStartZero && $loadMax-count($AppIdElected)>0 ){
			$TagTab							= $Ado->GetAll("SELECT * FROM big_app_tags WHERE app_id={$Arr['app_id']}");
			
			//标签池无任何资料
			if( empty($TagTab) ){
				$Json['status']				= true;
				$Json['error']				= '';
				$Json['val']				= array();
				break;
			}else{
				//表明资料为冷启动获取
				$Json['info']['choice'][]	= 'zero';
				//有资料,则根据标签个数平均分配每个标签获取的条数
				$loadRows					= ceil( $loadMax/count($TagTab) );
				foreach($TagTab as $Rs){
					$RsAll					= $Ado->SelectLimit("SELECT val_no,val_grade FROM big_value WHERE app_id={$Arr['app_id']} AND val_show='true' AND find_in_set('{$Rs}',val_tags) ORDER BY val_grade DESC",$loadRows);
					if( !empty($RsAll) ){
					foreach($RsAll as $Rss){
						$AppIdElected[$Rss['val_no']]		= $Rss['val_grade'];
					}
					}
				}
			}
			unset($TagTab,$Rs);
		}
		
		
		
		/**
		 * 热启动,用户获取
		 */
		if( ceil($AppRow['app_total_declaration'])>=$loadStartZero && preg_match($NoPreg, $Arr['user_no']) && $loadMax-count($AppIdElected)>0 ){
			//表明资料为游客热启动获取
			$Json['info']['choice'][]	= 'user';
			
			$Page						= 0;
			$Size						= 100;
			
			//搜索用户最热门的标签
			$EndTime					= date("Y-m-d H:i:s",time()-86400*$AppRow['app_reco_data']);
			$SQL						= "SELECT val_tags,COUNT(app_id) AS val_tags_num FROM `big_declaration` WHERE user_time_create>'{$EndTime}' AND user_no='{$UserNo}' GROUP BY val_tags ORDER BY val_tags_num";
			$Sum						= $Ado->GetOne("SELECT count(app_id) FROM `big_declaration` WHERE user_time_create>'{$EndTime}' AND user_no='{$UserNo}' GROUP BY val_tags ");
			$Tags						= array();
			$Lim						= 0;
			
			while( $Lim<$Sum ){
				$Page					= $Page+1;
				$Lim					= ($Page-1)*$Size;
				$Tab					= $Ado->SelectLimit($SQL, $Size, $Lim);
					
				if( !empty($Tab) ){
					foreach($Tab as $Rs){
						$TagsArr			= explode(",", $Rs['val_tags']);
						if( !empty($TagsArr) ){
							foreach($TagsArr as $Rss){
								if( !in_array($Rss, $Tags) ){
									$Tags[$Rss]	= ceil($Rs['val_tags_num']);
								}else{
									$Tags[$Rss]	= ceil($Tags[$Rss]) + ceil($Rs['val_tags_num']);
								}
							}
						}
					}
				}
			}
			unset($Tab);
			arsort($Tags);
			$TagsSum						= array_sum($Tags);
			
			//根据标签热门程度,开始搜索数据
			if( !empty($Tags) ){
				foreach($Tags as $Tag=>$Rs){
					$Page						= 0;
					$Lim						= 0;
					$Sum						= $Ado->GetOne("SELECT count(app_id) FROM `big_value` WHERE find_in_set('{$Tag}',val_tags)");
					
					
					//按照标签热门程度比例进行加载总数的比例设定
					$TagsMaxNum					= ($Rs / $TagsSum) * $loadMax;
					$TagsChoice					= 0;
					while( $Lim<$Sum && $TagsMaxNum>$TagsChoice ){
						
						$Page					= $Page+1;
						$Lim					= ($Page-1)*$Size;
						$Tab					= $Ado->SelectLimit("SELECT val_no,val_grade,val_tags FROM `big_value` WHERE val_show='true' AND find_in_set('{$Tag}',val_tags) ORDER BY val_time_update DESC", $Size, $Lim);
						if( !empty($Tab) ){
							foreach($Tab as $Rss){
								if( !isset($AppIdElected[$Rss['val_no']]) ){
									$AppIdElected[ $Rss['val_no'] ]	= $Rss['val_grade'];
									$TagsChoice++;
									
									$Tmp		= explode(",", $Rss['val_tags']);
									$ElectedTags= ( !empty($Tmp) )? array_merge($ElectedTags,$Tmp) : $ElectedTags;
								}
							}
						}
						unset($Tab,$Rss,$Tmp);
					}
				}
			}
			unset($Tags);
		}
		
		
		
		
		
		/**
		 * 热启动,游客获取
		 * 
		 */
		if( ceil($AppRow['app_total_declaration'])>=$loadStartZero && !preg_match($NoPreg, $Arr['user_no']) && preg_match($NoUserPreg, $Cok['bigrec_userno']) && $loadMax-count($AppIdElected)>0 ){
			
			//表明资料为游客热启动获取
			$Json['info']['choice'][]	= 'play';
			
			$Page						= 0;
			$Size						= 100;
			
			//搜索附近最热门的标签
			$EndTime					= date("Y-m-d H:i:s",time()-86400*$AppRow['app_reco_data']);
			$SQL						= "SELECT val_tags,ABS(user_ip - {$User['user_ip']}) AS user_ips,COUNT(app_id) AS val_tags_num FROM `big_declaration` WHERE user_time_create>'{$EndTime}' GROUP BY val_tags ORDER BY user_ips DESC";
			$Sum						= $Ado->GetOne("SELECT count(app_id) FROM `big_declaration` WHERE user_time_create>'{$EndTime}' GROUP BY val_tags ");
			$Tags						= array();
			$Lim						= 0;
			
			while( $Lim<$Sum ){
				$Page					= $Page+1;
				$Lim					= ($Page-1)*$Size;
				$Tab					= $Ado->SelectLimit($SQL, $Size, $Lim);
				
				if( !empty($Tab) ){
				foreach($Tab as $Rs){
					$TagsArr			= explode(",", $Rs['val_tags']);
					if( !empty($TagsArr) ){
					foreach($TagsArr as $Rss){
						if( !in_array($Rss, $Tags) ){
							$Tags[$Rss]	= ceil($Rs['val_tags_num']);
						}else{
							$Tags[$Rss]	= ceil($Tags[$Rss]) + ceil($Rs['val_tags_num']);
						}
					}
					}
				}
				}
			}
			unset($Tab,$Rs);
			arsort($Tags);
			$TagsSum						= array_sum($Tags);
			
			//根据标签热门程度,开始搜索数据
			if( !empty($Tags) ){
				foreach($Tags as $Tag=>$Rs){
					$Page						= 0;
					$Lim						= 0;
					$Sum						= $Ado->GetOne("SELECT count(app_id) FROM `big_value` WHERE find_in_set('{$Tag}',val_tags)");
					
					
					//按照标签热门程度比例进行加载总数的比例设定
					$TagsMaxNum					= ($Rs / $TagsSum) * $loadMax;
					$TagsChoice					= 0;
					while( $Lim<$Sum && $TagsMaxNum>$TagsChoice ){
						
						$Page					= $Page+1;
						$Lim					= ($Page-1)*$Size;
						$Tab					= $Ado->SelectLimit("SELECT val_no,val_grade,val_tags FROM `big_value` WHERE val_show='true' AND find_in_set('{$Tag}',val_tags) ORDER BY val_time_update DESC", $Size, $Lim);
						if( !empty($Tab) ){
							foreach($Tab as $Rss){
								if( !isset($AppIdElected[$Rss['val_no']]) ){
									$AppIdElected[ $Rss['val_no'] ]	= $Rss['val_grade'];
									$TagsChoice++;
									
									$Tmp		= explode(",", $Rss['val_tags']);
									$ElectedTags= ( !empty($Tmp) )? array_merge($ElectedTags,$Tmp) : $ElectedTags;
								}
							}
						}
						unset($Tab,$Rss,$Tmp);
					}
				}
			}
			unset($Tags);
		}
		
		
		
		
		
		//如果所有标签均查询完毕仍旧不够待选总数,则随机不足
		$Json['info']['choices']	= count($AppIdElected);
		$loadRows					= $loadMax - count($AppIdElected);
		if( $loadRows>0 ){
			$Tab					= $Ado->SelectLimit("SELECT val_no,val_grade FROM big_value WHERE app_id={$AppRow['app_id']} AND val_show='true' ORDER BY val_time_update DESC",$loadRows);
			if( !empty($Tab) ){
				foreach($Tab as $Rs){
					if( !isset($AppIdElected[$Rs['val_no']]) ){
						$AppIdElected[$Rs['val_no']]	= $Rs['val_grade'];
						$Json['info']['supplement'] 	= $Json['info']['supplement']+1;
					}
				}
			}
			unset($Tab,$Rs);
		}
		$Json['info']['elected']	= count($AppIdElected);
		
		
		
		
		//如果多样性数据小于等于加载需求数据,则启动多样性功能,加入类型热推
		if( $loadNonUniqueMini <= ($loadSize+count($TopChoice)) && $loadNonUniqueMini>0 ){
			$Json['info']['nonunique']		= 0;
			$NotTags						= $Ado->GetAll("SELECT * FROM big_app_tags WHERE app_id={$AppRow['app_id']} AND app_tags NOT IN (".FunToString($ElectedTags,",","'").")");
			
			if( !empty($NotTags) ){
				$ElectedTags				= array();
				$EndTime					= date("Y-m-d H:i:s",time()-86400*$AppRow['app_reco_data']);
				$NotMax						= ceil($loadMax * 0.1);
				$TagsChoice					= 0;
				
				foreach($NotTags as $Rs){
					$Page					= 0;
					$Lim					= 0;
					$Sum					= $Ado->GetOne("SELECT count(app_id) FROM `big_value` WHERE app_id={$AppRow['app_id']} AND  find_in_set('{$Rs['app_tags']}',val_tags)");
					$Size					= 100;
					
					if( $TagsChoice>$NotMax )break;
					while( $Lim<$Sum && $Lim<$NotMax ){
						$Page					= $Page+1;
						$Lim					= ($Page-1)*$Size;
						$Tab					= $Ado->SelectLimit("SELECT val_no FROM `big_value` WHERE app_id={$AppRow['app_id']} AND  find_in_set('{$Tag}',val_tags) ORDER BY val_time_update DESC", $Size, $Lim);
						if( !empty($Tab) ){
							foreach($Tab as $Rss){
								if( !isset($AppIdElected[$Rss['val_no']]) ){
									$AppIdElected[ $Rss['val_no'] ]	= 1;
									$Json['info']['nonunique']++;
								}
							}
						}
						
						if( $TagsChoice>$NotMax )break;
					}
					
					unset($Tab,$Rss);
				}
			}
			
		}
		
		
		
		
		//待选池载入完毕,开始随机选择数据
		arsort($AppIdElected);
		if( !empty($AppIdElected) ){
			$RandInt	= 0;
			foreach($AppIdElected as $Vs=>$Rs){
				$RandInt		   += $Rs+1;
				$AppIdElected[$Vs]	= $RandInt;
			}
		}
		unset($RandInt);
		if( $loadSize>=count($AppIdElected) ){
			$AppIdChoice		= array_keys($AppIdElected);
		}else{
			while( count($AppIdChoice)<$loadSize  ){
				//随机一个数字
				end($AppIdElected);
				$RandInt		= rand(0,current($AppIdElected));
				
				//根据该随机数字获得选中的ID
				foreach($AppIdElected as $Vs=>$Rs){
					if( $RandInt <= ceil($Rs) )   {$ChoiceID	= $Vs;break;}
				}
				if( !in_array($ChoiceID, $AppIdChoice) ){
					$AppIdChoice[]	= $ChoiceID;
				}
			}
		}
		
		//将固定选择部分加入 选择池 头部
		if( !empty($TopChoice) ){
			$TopChoiceID			= array();
			foreach($TopChoice as $Rs){
				$TopChoiceID[]		= $Rs['val_no'];
			}
			$AppIdChoice			= array_merge($TopChoiceID,$AppIdChoice);
			unset($TopChoice,$TopChoiceID);
		}
		
		
		//根据选择池加载资料列表
		$Tab					= $Ado->GetAll("SELECT * FROM big_value WHERE app_id={$Arr['app_id']} AND val_no IN (".FunToString($AppIdChoice,",","'").")");
		$Json['status']			= true;
		$Json['error']			= '';
		$Json['val']			= $Tab;
		
		
		$New					= array();
		if( !empty($Tab) ){
			foreach($AppIdChoice as $Rs){
				$Tr							= array();
				$Tr['app_id']				= $Arr['app_id'];
				$Tr['user_no']				= $UserNo;
				$Tr['val_no']				= $Rs;
				$Tr['user_ip']				= $User['user_ip'];
				$Tr['user_brower']			= $User['user_brower'];
				$Tr['user_os']				= $User['user_os'];
				$Tr['user_time_create']		= date("Y-m-d H:i:s");
				$New[]						= $Tr;
			}
			$Ado->AutoExecute('big_reocmmend', $New, 'INSERT');
		}
		break;
	break;
	
	
	
	
	
	
	
	
	
	/**
	 * 2.2	访问统计接口
	 */
	case "stat":
		$Arr					= LoadData('app_id,app_key,user_no,val_no');
		
		$ExistsApp				= ExistsApp($Arr['app_id'],null,$Arr['app_key']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		unset($Arr['app_key']);
		
	break;
	
	
	
	
	
	
	/**
	 * 2.3	访问上报接口
	 */
	case "up":
		$Arr					= LoadData('app_id,app_key,user_no,val_no,val_tags','get');
		$Cok					= LoadData('bigrec_userno','cookie');
		
		$ExistsApp				= ExistsApp($Arr['app_id'],null,$Arr['app_key']);
		if( $ExistsApp!==true ){
			$Json				= $ExistsApp;
			break;
		}
		unset($Arr['app_key']);
		
		if( !preg_match($NoPreg, $Arr['val_no']) ){
			$Json['status']		= false;
			$Json['error']		= '申报资料序号错误';
			break;
		}
		
		//如果没有传输用户序号,则强制写入游客序号
		if( !preg_match($NoPreg, $Arr['user_no'])&&!preg_match($NoUserPreg, $Cok['bigrec_userno']) ){
			$Json['status']		= false;
			$Json['error']		= '不允许不进行推荐立即执行上报措施';
			break;
		}
		
		//如果没有上报标签,则报错
		if( empty($Arr['val_tags']) ){
			$Json['status']		= false;
			$Json['error']		= '上报资料不全';
			break;
		}
		
		if( !preg_match($NoPreg, $Arr['user_no']) && preg_match($NoUserPreg, $Cok['bigrec_userno']) ){
			$Arr['user_no']			= $Cok['bigrec_userno'];
		}
		
		$Arr['user_ip']				= ip2long(FunGetTrueIP());
		$Arr['user_brower']			= FunGetBrowse();
		$Arr['user_os']				= FunGetOs();
		$Arr['user_time_create']	= date("Y-m-d H:i:s");
		
		$Ex							= $Ado->AutoExecute('big_declaration', $Arr, 'INSERT');
		
		if( $Ex ){
			$Json['status']			= true;
			$Json['error']			= '';
			
			$Ado->Execute("UPDATE big_app_cache SET app_total_declaration=app_total_declaration+1 WHERE app_id={$Arr['app_id']}");
		}else{
			$Json['status']			= false;
			$Json['error']			= '写入失败';
		}
	break;
}

echo json_encode($Json);