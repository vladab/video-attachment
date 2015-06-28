<?php
/*
Plugin Name: Video Attachment Settings
Plugin URI: https://github.com/vladab
Description: This Plugin Allows editing video upload information limiting file size limit and duration of the uploaded video file. ffmpeg is required for calculating duration
Version: 1.0.0
Author: Vladica Bibeskovic
Author URI: https://twitter.com/vlajko85
*/


class VideoAttachmentSettingsPlugin
{
    public static function init() {
        add_filter('wp_handle_upload_prefilter', array(get_class(), 'wp_handle_upload_prefilter'), 10, 1);
        add_filter('upload_mimes', array(get_class(), 'custom_upload_video_mimes'), 10, 1);
    }

    public static function get_attachment_max_size() {
        global $upload_max_size;
        $upload_max_size = get_option('upload_max_size', '300M');
        return $upload_max_size;
    }
    public static function get_video_max_duration() {
        return get_option('MAX_VIDEO_FILE_DURATION', '180'); // 180 = 3 min ..(60s x 3)
    }

    /**
     * @param $file
     * @uses wp_handle_upload_error
     * @return mixed|void
     */
    public static function wp_handle_upload_prefilter( $file ) {
        if( isset( $file['name'] ) && isset( $file['name'] ) ) {
            $type_mime = explode( '/', $file['type'] );

            if( $type_mime[0] == 'video') {
                $uploads = wp_upload_dir( null );
                $file_location = $uploads['path'] . "/{$file['name']}";
                include( __DIR__ . '/source/VideoInformation.php');
                $videoInfo = VideoInformation::getVideoInfo( $file_location );
                if( !empty( $videoInfo ) && isset( $videoInfo['duration'] ) ) {
                    if( intval($videoInfo['duration']) > self::get_video_max_duration() ) {
                        $file['error'] = __('Video File exceeds Specified Duration');
                        return $file;
                    }
                } else {
                    $file['error'] = __("Video file information couldn't be read.");
                }
            }
        }
        return apply_filters( 'wp_handle_upload_prefilter', $file );
    }

    public static function custom_upload_video_mimes( $mimes ) {
        $video_mimnes = array(
            // Video formats
            'asf|asx'       => 'video/x-ms-asf',
            'wmv'           => 'video/x-ms-wmv',
            'wmx'           => 'video/x-ms-wmx',
            'wm'            => 'video/x-ms-wm',
            'avi'           => 'video/avi',
            'divx'          => 'video/divx',
            'flv'           => 'video/x-flv',
            'mov|qt'        => 'video/quicktime',
            'mpeg|mpg|mpe'  => 'video/mpeg',
            'mp4|m4v'       => 'video/mp4',
            'ogv'           => 'video/ogg',
            'webm'          => 'video/webm',
            'mkv'           => 'video/x-matroska',
        );
        $mimes = array_merge($mimes, $video_mimnes );
        return $mimes;
    }
}

$upload_max_size = VideoAttachmentSettingsPlugin::get_attachment_max_size();
@ini_set( 'upload_max_size' , $upload_max_size );
@ini_set( 'post_max_size', $upload_max_size);

add_action('init', array( 'VideoAttachmentSettingsPlugin', 'init' ));
