<?php
/**
 * GoLinks Plugin
 *
 * @copyright  Copyright (c) 2013 DEFE (http://defe.me)
 * @license    GNU General Public License 2.0
 * 
 */

class Watermark_Action extends Typecho_Widget implements Widget_Interface_Do
{
    
    public function mark($img){
		
	$options = $this->widget('Widget_Options');
        $cfg = $options->plugin('Watermark');
        
        $img1 = $this->lujin( __TYPECHO_ROOT_DIR__ . base64_decode($img));        
	$dir='.'.__TYPECHO_PLUGIN_DIR__ . '/Watermark/';
		
	$ck_p = in_array('pic',$cfg->vm_type)?1:0;
	$ck_t = in_array('text',$cfg->vm_type)?1:0;
		
        $pos_p = $cfg->vm_pos_pic;
        $pos_t = $cfg->vm_pos_text;
        $font = $dir . $cfg->vm_font;
        $text = $cfg->vm_text;
        $size = $cfg->vm_size;
        $color = $cfg->vm_color;
        $mic_x = $cfg->vm_m_x;
        $mic_y = $cfg->vm_m_y;
        $width = $cfg->vm_width;
	$wmpic = $cfg->vm_pic?$cfg->vm_pic:'WM.png';	
        
        if(file_exists($img1)){
            require_once($dir.'class.php');
            $wm = new WaterMark();
            $wm->setImSrc($img1,$width); // 设置背景图		
            $wm->setImWater($dir.$wmpic); // 设置水印图
            $wm->setFont($font, $text, $size, $color); // 设置水印文字相关（字体库、文本、字体大小、颜色）
            if( isset($cfg->vm_cache) && 'cache' == $cfg->vm_cache){
                $file = base64_decode($img);
                $ext = pathinfo($file, PATHINFO_EXTENSION);
                $dir_cache =  __TYPECHO_ROOT_DIR__ . '/usr/img';
                if(!is_dir($dir_cache)) @mkdir($dir_cache, 0777);
                $file = './usr/img/'.md5($file).'.'.$ext;
                $wm->mark($ck_p, $pos_p, $ck_t, $pos_t, $mic_x, $mic_y, $file);                
            }else{
                $wm->mark($ck_p, $pos_p, $ck_t, $pos_t, $mic_x, $mic_y); // 设置是否打印图、位置；是否打印字体、位置
            }
        }else{
            $this->widget('Widget_Archive@404', 'type=404')->render();
        }
    }
    /**
     * 清除水印图片缓存
     * @return boolean
     */    
    public function clear(){
        $this->widget('Widget_User')->pass('administrator');
        $dir_cache = __TYPECHO_ROOT_DIR__. '/usr/img/';        
        if (is_writable($dir_cache)){
            chdir($dir_cache);
            $dh=opendir('.');
            $num = 0;
            while(false !== ($et=readdir($dh))){
                if(is_file($et)){
                    if(!@unlink($et)){
                        return false;
                        echo "缓存文件 {$et} 未能删除，请检查目录权限"; 
                        break;
                    }
                    echo "清除文件：{$et} <br>";
                    $num++;
                }               
            }           
            closedir($dh);
            echo "共清除 {$num} 个缓存文件<br>";       
            chdir('..');
            if(@rmdir('img')) echo '缓存文件目录已删除';             
        }
    }
    
    /**
     * 合并重复路径
     * @param string $uri
     * @return string
     */
    public function lujin($uri){
        $uri = str_replace("\\","/",$uri);
        $a = explode('/', $uri);
        $b = array_unique($a);
        return implode('/', $b);
    }

    public function action(){
        $this->on($this->request->is('mark'))->mark($this->request->mark);         
        $this->on($this->request->is('clear'))->clear();
    }
}

?>
