<?php
	class sysopen_apis_chebang_app {
		public function listUrl() {
			$mdlUrl = app::get('sysopen')->model('chebang_appurl');
			$lstUrl = $mdlUrl->getList('*');
			$list = array();
			foreach ($lstUrl as $url) {
				$tmp['url_id'] = $url['url_id'];
				$tmp['url_name'] = $url['url_name'];
				$tmp['url_tag'] = $url['url_tag'];
				$tmp['url'] = $url['url'];
				$list[] = $tmp;
			} 
			$result['errorcode'] = CHEBNAGAPI_CALL_SUCCESSED;
			$result['data'] = $list;
			
			return $result;
		}
	}
?>