<?php
require_once 'class.file.php';

/**
 * Description of class
 *
 * @author Srdjan
 *
 * $HeadURL: http://plugins.svn.wordpress.org/types/tags/1.6.6.2/embedded/common/toolset-forms/classes/class.video.php $
 * $LastChangedDate: 2014-11-18 06:47:25 +0000 (Tue, 18 Nov 2014) $
 * $LastChangedRevision: 1027712 $
 * $LastChangedBy: iworks $
 *
 */
class WPToolset_Field_Video extends WPToolset_Field_File
{
    protected $_settings = array('min_wp_version' => '3.6');

    public function metaform()
    {
        $validation = $this->getValidationData();
        $validation = self::addTypeValidation($validation);
        $this->setValidationData($validation);
        return parent::metaform();
    }

    public static function addTypeValidation($validation)
    {
        $valid_extensions = array(
            '3gp',
            'aaf',
            'asf',
            'avchd',
            'avi',
            'cam',
            'dat',
            'dsh',
            'fla',
            'flr',
            'flv',
            'm1v',
            'm2v',
            'm4v',
            'mng',
            'mp4',
            'mxf',
            'nsv',
            'ogg',
            'rm',
            'roq',
            'smi',
            'sol',
            'svi',
            'swf',
            'wmv',
            'wrap',
            'mkv',
            'mov',
            'mpe',
            'mpeg',
            'mpg',
        );
        $valid_extensions = apply_filters( 'toolset_valid_video_extentions', $valid_extensions);
        $validation['extension'] = array(
            'args' => array(
                'extension',
                implode('|', $valid_extensions),
            ),
            'message' => __( 'You can add only video.', 'wpv-views' ),
        );
        return $validation;
    }
}
