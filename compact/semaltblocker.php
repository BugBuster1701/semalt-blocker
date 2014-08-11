<?php

namespace Nabble;class Domainparser{private static$top_names='ac.cn,ac.jp,ac.uk,ad.jp,adm.br,adv.br,agr.br,ah.cn,am.br,arq.br,art.br,asn.au,ato.br,av.tr,bel.tr,bio.br,biz.tr,bj.cn,bmd.br,cim.br,cng.br,cnt.br,co.at,co.jp,co.uk,com.au,com.br,com.cn,com.eg,com.hk,com.mx,com.ru,com.tr,com.tw,conf.au,cq.cn,csiro.au,dr.tr,ecn.br,edu.au,edu.br,edu.tr,emu.id.au,eng.br,esp.br,etc.br,eti.br,eun.eg,far.br,fj.cn,fm.br,fnd.br,fot.br,fst.br,g12.br,gb.com,gb.net,gd.cn,gen.tr,ggf.br,gob.mx,gov.au,gov.br,gov.cn,gov.hk,gov.tr,gr.jp,gs.cn,gx.cn,gz.cn,ha.cn,hb.cn,he.cn,hi.cn,hk.cn,hl.cn,hn.cn,id.au,idv.tw,imb.br,ind.br,inf.br,info.au,info.tr,jl.cn,jor.br,js.cn,jx.cn,k12.tr,lel.br,ln.cn,ltd.uk,mat.br,me.uk,med.br,mil.br,mil.tr,mo.cn,mus.br,name.tr,ne.jp,net.au,net.br,net.cn,net.eg,net.hk,net.lu,net.mx,net.ru,net.tr,net.tw,net.uk,nm.cn,no.com,nom.br,not.br,ntr.br,nx.cn,odo.br,oop.br,or.at,or.jp,org.au,org.br,org.cn,org.hk,org.lu,org.ru,org.tr,org.tw,org.uk,plc.uk,pol.tr,pp.ru,ppg.br,pro.br,psc.br,psi.br,qh.cn,qsl.br,rec.br,sc.cn,sd.cn,se.com,se.net,sh.cn,slg.br,sn.cn,srv.br,sx.cn,tel.tr,tj.cn,tmp.br,trd.br,tur.br,tv.br,tw.cn,uk.com,uk.net,vet.br,wattle.id.au,web.tr,xj.cn,xz.cn,yn.cn,zj.cn,zlg.br,co.nr,co.nz,com.fr,';public static function parseUrl($url){$element=array('url','scheme','user','pass','domain','port','path','query','fragment');$r='!(?:(\w+)://)?(?:(\w+)\:(\w+)@)?([^/:]+)?';$r.='(?:\:(\d*))?([^#?]+)?(?:\?([^#]+))?(?:#(.+$))?!i';preg_match_all($r,$url,$out);$return=array();foreach($element as$n=>$v){$return[$v]=$out[$n][0];}$return['topleveldomain']=$return['subdomain']=$return['toplevelname']='';$return=empty($return['domain'])?$return:self::getDomain($return);$return['topleveldomain']=empty($return['topleveldomain'])?$return['domain']:$return['topleveldomain'];return$return;}private static function getDomain($host){if(($total_parts=substr_count($host['domain'],'.'))<=1){return$host;}$parts_array=explode('.',$host['domain']);$last_part=$parts_array[$total_parts];$test_part=$parts_array[--$total_parts].'.'.$last_part;if(strpos(self::$top_names,$test_part.',')){$last_part=$parts_array[--$total_parts].'.'.$test_part;if(strpos(self::$top_names,$last_part.',')){$host['toplevelname']=$last_part;$last_part=$parts_array[--$total_parts].'.'.$last_part;$host['topleveldomain']=$last_part;$host['subdomain']=str_ireplace('.'.$last_part,'',$host['domain']);}else{$host['topleveldomain']=$last_part;$host['subdomain']=str_ireplace('.'.$last_part,'',$host['domain']);$host['toplevelname']=$test_part;}}else{$host['topleveldomain']=$test_part;$host['subdomain']=str_ireplace('.'.$test_part,'',$host['domain']);$host['toplevelname']=$last_part;}return$host;}}namespace Nabble;class Semalt{public static$blocklist='./../domains/blocked';private static$debug='Not blocking, no reason given';public static function block($action=false){if(self::isRefererOnBlocklist()){self::cls();if(filter_var($action,FILTER_VALIDATE_URL)){self::redirect($action);}else{self::forbidden();if(!empty($action))echo$action;}exit;}}public static function blocked($verbose=false){$blocked=self::isRefererOnBlocklist();if($verbose===true){return self::$debug;}return$blocked;}public static function getBlocklist(){return self::parseBlocklist(self::getBlocklistContents());}private function cls(){while(ob_get_level())ob_end_clean();}private static function redirect($url){header("Location: ".$url);}private static function forbidden(){$protocol=(isset($_SERVER['SERVER_PROTOCOL'])?$_SERVER['SERVER_PROTOCOL']:'HTTP/1.0');header($protocol.' 403 Forbidden');}private static function isRefererOnBlocklist(){$referer=self::getHttpReferer();if($referer===false){self::$debug="Not blocking because referral header is not set or empty";return false;}$rootDomain=self::getRootDomain($referer);if($rootDomain===false){self::$debug="Not blocking because we couldn't parse referral domain";return false;}if(!in_array($rootDomain,static::getBlocklist())){self::$debug="Not blocking because referral domain (".$rootDomain.") is not found on blocklist";return false;}self::$debug="Blocking because referral domain (".$rootDomain.") is found on blocklist";return true;}private static function getRootDomain($url){$urlParts=Domainparser::parseUrl($url);return(isset($urlParts['topleveldomain'])&&!empty($urlParts['topleveldomain']))?$urlParts['topleveldomain']:false;}private static function getHttpReferer(){if(isset($_SERVER['HTTP_REFERER'])&&!empty($_SERVER['HTTP_REFERER'])){return$_SERVER['HTTP_REFERER'];}return false;}private static function getBlocklistFilename(){return __DIR__.DIRECTORY_SEPARATOR.static::$blocklist;}private static function getBlocklistContents(){$blocklistContent=file_get_contents(self::getBlocklistFilename());return$blocklistContent;}private static function parseBlocklist($blocklistContent){return array_map('trim',array_filter(explode(PHP_EOL,$blocklistContent)));}}