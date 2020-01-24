<?php
namespace SurprisePhp\Oss\Helpers;

/**
 * Message helper class
 *
 * @package   Uploader
 * @subpackage   Uploader\Helpers
 * @since     PHP >=5.4
 * @version   1.0
 * @author    Stanislav WEB | Lugansk <stanisov@gmail.com>
 * @copyright Stanislav WEB
 */
class Message
{
    /**
     * Error messages collect
     *
     * @access private
     * @var array
     */
    private static $messages = [
        'INVALID_MIN_SIZE'       => '文件 %s 过小，至少为 %s',
        'INVALID_MAX_SIZE'       => '文件 %s 过大, 最大为 %s',
        'INVALID_EXTENSION'      => '文件 %s 格式不正确. 仅支持: %s',
        'INVALID_MIME_TYPES'     => '文件 %s 格式不正确. 仅支持: %s',
        'INVALID_UPLOAD_DIR'     => 'The specified directory %s is not a directory download',
        'INVALID_PERMISSION_DIR' => 'The specified directory %s is not writable',
    ];

    /**
     * Get message
     *
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        if (isset(self::$messages[$key]) === true) {
            return self::$messages[$key];
        }
    }
}
