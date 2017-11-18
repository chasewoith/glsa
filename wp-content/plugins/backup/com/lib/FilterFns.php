<?php
require_once(SG_LIB_PATH.'CallbackFilter.php');

function sgappend($stream, $callback, $read_write = STREAM_FILTER_ALL)
{
    $ret = @stream_filter_append($stream, sgregister(), $read_write, $callback);

    if ($ret === false) {
        $error = error_get_last() + array('message' => '');
        throw new RuntimeException('Unable to append filter: ' . $error['message']);
    }

    return $ret;
}

function sgprepend($stream, $callback, $read_write = STREAM_FILTER_ALL)
{
    $ret = @stream_filter_prepend($stream, sgregister(), $read_write, $callback);

    if ($ret === false) {
        $error = error_get_last() + array('message' => '');
        throw new RuntimeException('Unable to prepend filter: ' . $error['message']);
    }

    return $ret;
}

function sgfun($filter, $params = null)
{
    $fp = fopen('php://memory', 'w');
    $filter = @stream_filter_append($fp, $filter, STREAM_FILTER_WRITE, $params);

    if ($filter === false) {
        fclose($fp);
        $error = error_get_last() + array('message' => '');
        throw new RuntimeException('Unable to access built-in filter: ' . $error['message']);
    }

    // append filter function which buffers internally
    $buffer = '';
    sgappend($fp, function ($chunk) use (&$buffer) {
        $buffer .= $chunk;

        // always return empty string in order to skip actually writing to stream resource
        return '';
    }, STREAM_FILTER_WRITE);

    $closed = false;

    return function ($chunk = null) use ($fp, $filter, &$buffer, &$closed) {
        if ($closed) {
            throw new RuntimeException('Unable to perform operation on closed stream');
        }
        if ($chunk === null) {
            $closed = true;
            $buffer = '';
            fclose($fp);
            return $buffer;
        }
        // initialize buffer and invoke filters by attempting to write to stream
        $buffer = '';
        fwrite($fp, $chunk);

        // buffer now contains everything the filter function returned
        return $buffer;
    };
}

function sgremove($filter)
{
    if (@stream_filter_remove($filter) === false) {
        throw new RuntimeException('Unable to remove given filter');
    }
}

function sgregister()
{
    static $registered = null;
    if ($registered === null) {
        $registered = 'stream-callback';
        stream_filter_register($registered, 'CallbackFilter');
    }
    return $registered;
}
