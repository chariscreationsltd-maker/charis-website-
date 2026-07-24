<?php
/**
 * Plugin Name: Charis Gallery API
 * Description: FileBird folder-based gallery API
 * Version: 1.0
 */
add_action('rest_api_init', function () {
    register_rest_route('charis/v1', '/gallery/(?P<folder_id>\d+)', array('methods'=>'GET','callback'=>'charis_get_gallery_images','permission_callback'=>'__return_true'));
    register_rest_route('charis/v1', '/folders', array('methods'=>'GET','callback'=>'charis_get_folders','permission_callback'=>'__return_true'));
});
function charis_get_gallery_images($request) {
    global $wpdb;
    $folder_id = intval($request['folder_id']);
    $per_page = min(intval($request->get_param('per_page') ?: 60), 200);
    $table = $wpdb->prefix . 'fbv_attachment_folder';
    $ids = $wpdb->get_col($wpdb->prepare("SELECT attachment_id FROM {$table} WHERE folder_id=%d ORDER BY attachment_id DESC LIMIT %d",$folder_id,$per_page));
    if(empty($ids)) return new WP_REST_Response(array(),200);
    $images=array();
    foreach($ids as $id){
        $id=intval($id);
        $mime=get_post_mime_type($id);
        if(!$mime||strpos($mime,'image/')!==0) continue;
        $full=wp_get_attachment_image_src($id,'full');
        $large=wp_get_attachment_image_src($id,'large');
        $thumb=wp_get_attachment_image_src($id,'medium');
        if(!$full) continue;
        $title=get_the_title($id);
        $images[]=array('id'=>$id,'src'=>$full[0],'large'=>$large?$large[0]:$full[0],'thumb'=>$thumb?$thumb[0]:$full[0],'width'=>$full[1],'height'=>$full[2],'title'=>$title,'alt'=>get_post_meta($id,'_wp_attachment_image_alt',true)?:$title,'caption'=>wp_get_attachment_caption($id)?:'');
    }
    return new WP_REST_Response($images,200);
}
function charis_get_folders($request) {
    global $wpdb;
    $folders=$wpdb->get_results("SELECT id,name,parent FROM {$wpdb->prefix}fbv ORDER BY name");
    $r=array();
    foreach((array)$folders as $f) $r[]=array('id'=>intval($f->id),'name'=>$f->name,'parent'=>intval($f->parent));
    return new WP_REST_Response($r,200);
}