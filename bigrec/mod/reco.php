<?php

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


$Arr					= LoadData('app_id,app_key,user_no,size','get');
$Cok					= LoadData('bigrec_userno','cookie');

$ExistsApp				= ExistsApp($Arr['app_id'],null,$Arr['app_key']);
if( $ExistsApp!==true ){
	$Json				= $ExistsApp;
	output($Json);
}
$AppID					= $Arr['app_id'];
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
$TopChoice							= $Ado->SelectLimit("SELECT * FROM big{$AppID}_pro WHERE pro_show='true' AND pro_grade=9 ORDER BY pro_time_update DESC",$loadSize);
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
	$TagTab							= $Ado->GetAll("SELECT * FROM big{$AppID}_app_tags");
		
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
			$RsAll					= $Ado->SelectLimit("SELECT pro_no,pro_grade FROM big{$AppID}_pro WHERE pro_show='true' AND find_in_set('{$Rs}',pro_tags) ORDER BY pro_grade DESC",$loadRows);
			if( !empty($RsAll) ){
				foreach($RsAll as $Rss){
					$AppIdElected[$Rss['pro_no']]		= $Rss['pro_grade'];
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
	$SQL						= "SELECT pro_tags,COUNT(*) AS pro{$AppID}_tags_num FROM `big{$AppID}_declaration` WHERE user_time_create>'{$EndTime}' AND user_no='{$UserNo}' GROUP BY pro_tags ORDER BY pro_tags_num";
	$Sum						= $Ado->GetOne("SELECT count(*) FROM `big{$AppID}_declaration` WHERE user_time_create>'{$EndTime}' AND user_no='{$UserNo}' GROUP BY pro_tags ");
	$Tags						= array();
	$Lim						= 0;
		
	while( $Lim<$Sum ){
		$Page					= $Page+1;
		$Lim					= ($Page-1)*$Size;
		$Tab					= $Ado->SelectLimit($SQL, $Size, $Lim);
			
		if( !empty($Tab) ){
			foreach($Tab as $Rs){
				$TagsArr			= explode(",", $Rs['pro_tags']);
				if( !empty($TagsArr) ){
					foreach($TagsArr as $Rss){
						if( !in_array($Rss, $Tags) ){
							$Tags[$Rss]	= ceil($Rs['pro_tags_num']);
						}else{
							$Tags[$Rss]	= ceil($Tags[$Rss]) + ceil($Rs['pro_tags_num']);
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
			$Sum						= $Ado->GetOne("SELECT count(*) FROM `big{$AppID}_pro` WHERE find_in_set('{$Tag}',pro_tags)");
				
				
			//按照标签热门程度比例进行加载总数的比例设定
			$TagsMaxNum					= ($Rs / $TagsSum) * $loadMax;
			$TagsChoice					= 0;
			while( $Lim<$Sum && $TagsMaxNum>$TagsChoice ){

				$Page					= $Page+1;
				$Lim					= ($Page-1)*$Size;
				$Tab					= $Ado->SelectLimit("SELECT pro_no,pro_grade,pro_tags FROM `big{$AppID}_pro` WHERE pro_show='true' AND find_in_set('{$Tag}',pro_tags) ORDER BY pro_time_update DESC", $Size, $Lim);
				if( !empty($Tab) ){
					foreach($Tab as $Rss){
						if( !isset($AppIdElected[$Rss['pro_no']]) ){
							$AppIdElected[ $Rss['pro_no'] ]	= $Rss['pro_grade'];
							$TagsChoice++;
								
							$Tmp		= explode(",", $Rss['pro_tags']);
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
	$SQL						= "SELECT pro_tags,ABS(user_ip - {$User['user_ip']}) AS user_ips,COUNT(app_id) AS pro_tags_num FROM `big_declaration` WHERE user_time_create>'{$EndTime}' GROUP BY pro_tags ORDER BY user_ips DESC";
	$Sum						= $Ado->GetOne("SELECT count(app_id) FROM `big_declaration` WHERE user_time_create>'{$EndTime}' GROUP BY pro_tags ");
	$Tags						= array();
	$Lim						= 0;
		
	while( $Lim<$Sum ){
		$Page					= $Page+1;
		$Lim					= ($Page-1)*$Size;
		$Tab					= $Ado->SelectLimit($SQL, $Size, $Lim);

		if( !empty($Tab) ){
			foreach($Tab as $Rs){
				$TagsArr			= explode(",", $Rs['pro_tags']);
				if( !empty($TagsArr) ){
					foreach($TagsArr as $Rss){
						if( !in_array($Rss, $Tags) ){
							$Tags[$Rss]	= ceil($Rs['pro_tags_num']);
						}else{
							$Tags[$Rss]	= ceil($Tags[$Rss]) + ceil($Rs['pro_tags_num']);
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
			$Sum						= $Ado->GetOne("SELECT count(app_id) FROM `big_value` WHERE find_in_set('{$Tag}',pro_tags)");
				
				
			//按照标签热门程度比例进行加载总数的比例设定
			$TagsMaxNum					= ($Rs / $TagsSum) * $loadMax;
			$TagsChoice					= 0;
			while( $Lim<$Sum && $TagsMaxNum>$TagsChoice ){

				$Page					= $Page+1;
				$Lim					= ($Page-1)*$Size;
				$Tab					= $Ado->SelectLimit("SELECT pro_no,pro_grade,pro_tags FROM `big_value` WHERE pro_show='true' AND find_in_set('{$Tag}',pro_tags) ORDER BY pro_time_update DESC", $Size, $Lim);
				if( !empty($Tab) ){
					foreach($Tab as $Rss){
						if( !isset($AppIdElected[$Rss['pro_no']]) ){
							$AppIdElected[ $Rss['pro_no'] ]	= $Rss['pro_grade'];
							$TagsChoice++;
								
							$Tmp		= explode(",", $Rss['pro_tags']);
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
	$Tab					= $Ado->SelectLimit("SELECT pro_no,pro_grade FROM big_value WHERE app_id={$AppRow['app_id']} AND pro_show='true' ORDER BY pro_time_update DESC",$loadRows);
	if( !empty($Tab) ){
		foreach($Tab as $Rs){
			if( !isset($AppIdElected[$Rs['pro_no']]) ){
				$AppIdElected[$Rs['pro_no']]	= $Rs['pro_grade'];
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
			$Sum					= $Ado->GetOne("SELECT count(app_id) FROM `big_value` WHERE app_id={$AppRow['app_id']} AND  find_in_set('{$Rs['app_tags']}',pro_tags)");
			$Size					= 100;
				
			if( $TagsChoice>$NotMax )break;
			while( $Lim<$Sum && $Lim<$NotMax ){
				$Page					= $Page+1;
				$Lim					= ($Page-1)*$Size;
				$Tab					= $Ado->SelectLimit("SELECT pro_no FROM `big_value` WHERE app_id={$AppRow['app_id']} AND  find_in_set('{$Tag}',pro_tags) ORDER BY pro_time_update DESC", $Size, $Lim);
				if( !empty($Tab) ){
					foreach($Tab as $Rss){
						if( !isset($AppIdElected[$Rss['pro_no']]) ){
							$AppIdElected[ $Rss['pro_no'] ]	= 1;
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
		$TopChoiceID[]		= $Rs['pro_no'];
	}
	$AppIdChoice			= array_merge($TopChoiceID,$AppIdChoice);
	unset($TopChoice,$TopChoiceID);
}


//根据选择池加载资料列表
$Tab					= $Ado->GetAll("SELECT * FROM big_value WHERE app_id={$Arr['app_id']} AND pro_no IN (".FunToString($AppIdChoice,",","'").")");
$Json['status']			= true;
$Json['error']			= '';
$Json['val']			= $Tab;


$New					= array();
if( !empty($Tab) ){
	foreach($AppIdChoice as $Rs){
		$Tr							= array();
		$Tr['app_id']				= $Arr['app_id'];
		$Tr['user_no']				= $UserNo;
		$Tr['pro_no']				= $Rs;
		$Tr['user_ip']				= $User['user_ip'];
		$Tr['user_brower']			= $User['user_brower'];
		$Tr['user_os']				= $User['user_os'];
		$Tr['user_time_create']		= date("Y-m-d H:i:s");
		$New[]						= $Tr;
	}
	$Ado->AutoExecute('big_reocmmend', $New, 'INSERT');
}

output($Json);