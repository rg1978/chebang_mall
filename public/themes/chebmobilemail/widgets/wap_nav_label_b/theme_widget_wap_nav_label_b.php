<?php

function theme_widget_wap_nav_label_b(&$setting){
    $theme_dir = kernel::get_themes_host_url().'/'.theme::getThemeName();
    $setting['themeUrl'] = $theme_dir;
    //echo '<pre>';print_r($setting);exit();
    if($setting['pic_1st'][1]['tag']!=''){
	    foreach($setting['pic_1st'] as $key=>$pic_1st)
	    {
	        $setting['pic_1st'][$key]['linktarget'] = url::to($pic_1st['linktarget']);
	    }
	    $setting['pic_1st']['floor_ture'] = 1;
	}

    if($setting['pic_2nd'][1]['tag']!=''){
	    foreach($setting['pic_2nd'] as $key=>$pic_2nd)
	    {
	        $setting['pic_2nd'][$key]['linktarget'] = url::to($pic_2nd['linktarget']);
	    }
	    $setting['pic_2nd']['floor_ture'] = 1;
	}

	// if($setting['pic_3rd'][1]['tag']!=''){
	//     foreach($setting['pic_3rd'] as $key=>$pic_3rd)
	//     {
	//         $setting['pic_3rd'][$key]['linktarget'] = url::to($pic_3rd['linktarget']);
	//     }
	//     $setting['pic_3rd']['floor_ture'] = 1;
	// }

	// if($setting['pic_4th'][1]['tag']!=''){
	//     foreach($setting['pic_4th'] as $key=>$pic_4th)
	//     {
	//         $setting['pic_4th'][$key]['linktarget'] = url::to($pic_4th['linktarget']);
	//     }
	//     $setting['pic_4th']['floor_ture'] = 1;
	// }

	// if($setting['pic_5th'][1]['tag']!=''){
	//     foreach($setting['pic_5th'] as $key=>$pic_5th)
	//     {
	//         $setting['pic_5th'][$key]['linktarget'] = url::to($pic_5th['linktarget']);
	//     }
	//     $setting['pic_5th']['floor_ture'] = 1;
	// }
	//dump($setting);
    return $setting;
}
?>
