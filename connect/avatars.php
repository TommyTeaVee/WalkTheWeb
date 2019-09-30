<?php
require_once('../core/functions/class_wtwconnect.php');
global $wtwconnect;
try {
	/* google analytics tracking (if defined in wtw_config.php) */
	$wtwconnect->trackPageView($wtwconnect->domainurl."/connect/avatars.php");

	/* get values from querystring or session */
	$zinstanceid = $wtwconnect->getVal('i','');
	$zuserid = $wtwconnect->getVal('d','');
	$zuserip = $wtwconnect->getVal('p','');
	$zuseravatarid = "";
	$znewuseravatarid = "";
	if ((empty($zuserid) || !isset($zuserid)) && !empty($zinstanceid) && isset($zinstanceid)) {
		/* check for anonymous avatar with same instanceid - not logged in user (latest used) */
		$zresults = $wtwconnect->query("
			select useravatarid 
			from ".wtw_tableprefix."useravatars 
			where instanceid='".$zinstanceid."' 
				and userid='' 
				and deleted=0 
			order by updatedate desc 
			limit 1;");
		foreach ($zresults as $zrow) {
			$zuseravatarid = $zrow["useravatarid"];
		}
	}
	if ((empty($zuseravatarid) || !isset($zuseravatarid)) && (!empty($zuserid) && isset($zuserid)) && !empty($zinstanceid) && isset($zinstanceid)) {
		/* check for avatar with same instanceid for logged in user (latest used) */
		$zresults = $wtwconnect->query("
			select useravatarid 
			from ".wtw_tableprefix."useravatars 
			where userid='".$zuserid."' 
				and instanceid='".$zinstanceid."' 
				and deleted=0 
			order by updatedate desc 
			limit 1;");
		foreach ($zresults as $zrow) {
			$zuseravatarid = $zrow["useravatarid"];
		}
	}
	if ((empty($zuseravatarid) || !isset($zuseravatarid)) && !empty($zinstanceid) && isset($zinstanceid)) {
		/* check for anonymous avatar with same instanceid for user logged in (latest used) */
		$zresults = $wtwconnect->query("
			select useravatarid 
			from ".wtw_tableprefix."useravatars 
			where instanceid='".$zinstanceid."' 
				and userid='' 
				and deleted=0 
			order by updatedate desc 
			limit 1;");
		foreach ($zresults as $zrow) {
			$zuseravatarid = $zrow["useravatarid"];
		}
	}
	if ((empty($zuseravatarid) || !isset($zuseravatarid)) && (!empty($zuserid) && isset($zuserid))) {
		/* check for avatar for logged in user with any instance (latest used) */
		$zresults = $wtwconnect->query("
			select useravatarid 
			from ".wtw_tableprefix."useravatars 
			where userid='".$zuserid."' 
				and (not userid='') 
				and deleted=0 
			order by updatedate desc 
			limit 1;");
		foreach ($zresults as $zrow) {
			$zuseravatarid = $zrow["useravatarid"];
		}
	}
	$i = 0;
	$zavatar = array();
	$zavatarparts = array();
	$zresponse = null;
	$zavataranimationdefs = array();
	$zavatarind = -1;
	$zscalingx = '.07';
	$zscalingy = '.07';
	$zscalingz = '.07';
	$zobjectfolder = "";
	$zobjectfile = "";
	$zdisplayname = "Anonymous";
	$zprivacy = 0;

	echo $wtwconnect->addConnectHeader($wtwconnect->domainname);

	if (!empty($zuseravatarid) && isset($zuseravatarid)) {
		/* get avatar and color settings */
		$zresults = $wtwconnect->query("
			select a.*,
				c.avatarpartid,
				c.avatarpart,
				c.emissivecolorr,
				c.emissivecolorg,
				c.emissivecolorb
			from ".wtw_tableprefix."useravatars a 
				left join ".wtw_tableprefix."useravatarcolors c
					on a.useravatarid = c.useravatarid
			where a.useravatarid='".$zuseravatarid."'
				and (c.deleted is null or c.deleted=0)
			order by c.avatarpart, c.updatedate desc;");
		foreach ($zresults as $zrow) {
			$zuseravatarid = $zrow["useravatarid"];
			$zinstanceid = $zrow["instanceid"];
			$zavatarind = $zrow["avatarind"];
			$zscalingx = $zrow["scalingx"];
			$zscalingy = $zrow["scalingy"];
			$zscalingz = $zrow["scalingz"];
			$zobjectfolder = $zrow["objectfolder"];
			$zobjectfile = $zrow["objectfile"];
			$zdisplayname = $zrow["displayname"];
			$zprivacy = $zrow["privacy"];
			$zavatarparts[$i] = array(
				'avatarpartid'=> $zrow["avatarpartid"],
				'avatarpart'=> $zrow["avatarpart"],
				'emissivecolorr'=> $zrow["emissivecolorr"],
				'emissivecolorg'=> $zrow["emissivecolorg"],
				'emissivecolorb'=> $zrow["emissivecolorb"]
			);
			$i += 1;
		}
		$i = 0;
		/* get avatar animations */
		$zresults = $wtwconnect->query("
			select u.*,
				a.loadpriority,
				a.animationfriendlyname,
				a.animationicon,
				a.objectfolder,
				a.objectfile,
				a.startframe,
				a.endframe,
				a.animationloop,
				a.speedratio as defaultspeedratio,
				a.soundid,
				a.soundpath,
				a.soundmaxdistance
			from ".wtw_tableprefix."useravataranimations u 
				inner join ".wtw_tableprefix."avataranimations a
					on u.avataranimationid=a.avataranimationid
			where u.useravatarid='".$zuseravatarid."'
				and u.deleted=0
			order by a.loadpriority desc, u.avataranimationname, u.avataranimationid, u.useravataranimationid;");
		foreach ($zresults as $zrow) {
			$zavataranimationdefs[$i] = array(
				'animationind'=> $i,
				'useravataranimationid'=> $zrow["useravataranimationid"],
				'avataranimationid'=> $zrow["avataranimationid"],
				'animationname'=> $zrow["avataranimationname"],
				'animationfriendlyname'=> $zrow["animationfriendlyname"],
				'loadpriority'=> $zrow["loadpriority"],
				'animationicon'=> $zrow["animationicon"],
				'defaultspeedratio'=> $zrow["defaultspeedratio"],
				'speedratio'=> $zrow["speedratio"],
				'objectfolder'=> $zrow["objectfolder"],
				'objectfile'=> $zrow["objectfile"],
				'startframe'=> $zrow["startframe"],
				'endframe'=> $zrow["endframe"],
				'animationloop'=> $zrow["animationloop"],
				'walkspeed'=> $zrow["walkspeed"]
			);
			$i += 1;
		}
	}
	
	/* combine avatar settings and animations for json return dataset */
	$zavatar = array(
		'userid'=> $zuserid,
		'useravatarid'=> $zuseravatarid,
		'instanceid'=> $zinstanceid,
		'avatarind'=> $zavatarind,
		'scalingx'=> $zscalingx,
		'scalingy'=> $zscalingy,
		'scalingz'=> $zscalingz,
		'objectfolder'=> $zobjectfolder,
		'objectfile'=> $zobjectfile,
		'displayname'=> $zdisplayname,
		'privacy'=> $zprivacy,
		'avatarparts'=> $zavatarparts,
		'avataranimationdefs'=> $zavataranimationdefs
	);
	$zresponse['avatar'] = $zavatar;
	echo json_encode($zresponse);	
} catch (Exception $e) {
	$wtwconnect->serror("connect-avatars.php=".$e->getMessage());
}
?>