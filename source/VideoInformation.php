<?php
/**
 * Project: gofanding.com
 *
 * Created by vladicabibeskovic.
 * Date: 21.6.15., 21.23 
 */

class VideoInformation {

    public static function getVideoInfo( $video_file_path ){

        $info_command = "ffprobe -v quiet '$video_file_path' -print_format json -show_format -show_streams 2>&1 ";
        ob_start();
        passthru($info_command);
        $ffmpeg_output = ob_get_contents();
        ob_end_clean();
        if( isset( $ffmpeg_output ) && $ffmpeg_output != '' ) {
            $file_info = json_decode($ffmpeg_output);
            $info = array();
            if( isset($file_info->streams[0]->width) ) {
                $info['width'] = (int)$file_info->streams[0]->width;
            } else if( isset($file_info->streams[1]->width) ) {
                $info['width'] = (int)$file_info->streams[1]->width;
            }
            if( isset($file_info->streams[0]->height) ) {
                $info['height'] = (int)$file_info->streams[0]->height;
            } else if( isset($file_info->streams[1]->height) ) {
                $info['height'] = (int)$file_info->streams[1]->height;
            }
            if( isset($file_info->streams[0]->avg_frame_rate) ) {
                $info['framerate'] = (int)$file_info->streams[0]->avg_frame_rate;
            } else if( isset($file_info->streams[1]->avg_frame_rate) ) {
                $info['framerate'] = (int)$file_info->streams[1]->avg_frame_rate;
            }
            if( isset($file_info->streams[0]->bit_rate) ) {
                $info['bitrate'] = (int)$file_info->streams[0]->bit_rate;
            }
            if( isset($file_info->streams[0]->duration) ) {
                $info['duration'] = (int)$file_info->streams[0]->duration;
            }
            return $info;
        } else {
            return false;
        }
    }
}