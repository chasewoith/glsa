<?php
require_once(SG_LIB_PATH.'BigInteger.php');
require_once(SG_LIB_PATH.'FilterFns.php');

interface SGArchiveDelegate
{
	public function getCorrectCdrFilename($filename);
	public function didExtractFile($filePath);
    public function didCountFilesInsideArchive($count);
    public function didFindExtractError($error);
}

class SGArchive
{
	const VERSION = 1;
	const CHUNK_SIZE = 1048576; //1mb
	private $filePath = '';
	private $mode = '';
	private $fileHandle = null;
	private $cdrFileHandle = null;
	private $cdrFilesCount = 0;
	private $cdr = array();
	private $fileOffset = null;
	private $delegate;
	private $zoffsets = array();
	private $totalWrittenBytes;

	public function __construct($filePath, $mode, $stateData = array())
	{
		$this->filePath = $filePath;
		$this->totalWrittenBytes = new Math_BigInteger(0);

		if ($mode=='w')
		{
			$cdrPath = $filePath.'.cdr';
			if (file_exists($cdrPath))
			{
				$mode = 'a';
			}

			$this->cdrFileHandle = @fopen($cdrPath, $mode.'b');
		}

		$this->fileHandle = @fopen($filePath, $mode.'b');
		$this->mode = $mode;
		$this->clear();

		$this->cdrFilesCount = isset($stateData['cdrSize'])?$stateData['cdrSize']:0;
		$this->zoffsets = isset($stateData['zoffsets'])?$stateData['zoffsets']:array();
	}

	public function setDelegate(SGArchiveDelegate $delegate)
	{
		$this->delegate = $delegate;
	}

	public function addFileFromPath($filename, $path, $stateData)
	{
		$fp = fopen($path, 'rb');
		$chunk = isset($stateData['chunk'])?(int)$stateData['chunk']:0;
		$deflate = sgfun('zlib.deflate');

		if ($chunk > 0)
		{
			fseek($fp, $chunk*self::CHUNK_SIZE);
			$zlen = new Math_BigInteger($stateData['zlen']);
		}
		else
		{
			$zlen = new Math_BigInteger(0);
		}

		$notificationCenter = SGNotificationCenter::getInstance();

		//read file in small chunks
		while (!feof($fp))
		{
			if ($notificationCenter->postAskNotification(SG_NOTIFICATION_SHOULD_CONTINUE_ACTION)===false)
            {
            	$data = $deflate();
				$this->write($data);
				if ($chunk>0) $zlen = $zlen->add(new Math_BigInteger(strlen($data)));
				$this->totalWrittenBytes = $this->totalWrittenBytes->add(new Math_BigInteger(strlen($data)));

	            fclose($fp);
	            fclose($this->fileHandle);

            	$this->zoffsets[] = $this->totalWrittenBytes->toString();
                $notificationCenter->postDoNotification(SG_NOTIFICATION_SAVE_FILES_ACTION_STATE, array(
                	'chunk'=>$chunk,
	                'filename'=>$filename,
	                'path'=>$path,
	                'cdrSize'=>$this->cdrFilesCount,
	                'zoffsets'=>$this->zoffsets,
	                'zlen'=>$zlen->toString()
	            ));

                throw new SGExceptionPause();
            }

			$data = fread($fp, self::CHUNK_SIZE);
			$data = $deflate($data);
			$this->write($data);

			$zlen = $zlen->add(new Math_BigInteger(strlen($data)));
			$this->totalWrittenBytes = $this->totalWrittenBytes->add(new Math_BigInteger(strlen($data)));

			$chunk++;
		}

		$data = $deflate();
		$this->write($data);
		$zlen = $zlen->add(new Math_BigInteger(strlen($data)));
		$this->totalWrittenBytes = $this->totalWrittenBytes->add(new Math_BigInteger(strlen($data)));

		fclose($fp);

		$len = new Math_BigInteger(0);
		$this->addFileToCdr($filename, $zlen, $len);
	}

	public function addFile($filename, $data)
	{
		$deflate = sgfun('zlib.deflate');

		if ($data)
		{
			$data = $deflate($data).$deflate();
			$this->write($data);
		}

		$zlen = new Math_BigInteger(strlen($data));
		$len = new Math_BigInteger(0);

		$this->addFileToCdr($filename, $zlen, $len);
	}

	private function addFileToCdr($filename, $zlen, $len)
	{
		//store cdr data for later use
		$this->addToCdr($filename, $zlen, $len);

		$this->fileOffset = $this->fileOffset->add($zlen);
	}

	public function finalize()
	{
		$this->addFooter();

		fclose($this->fileHandle);

		$this->clear();
	}

	private function addFooter()
	{
		$footer = '';

		//save version
		$footer .= $this->packToLittleEndian(self::VERSION, 1);

		//save extra
		$extra = implode(',', $this->zoffsets);
		$footer .= $this->packToLittleEndian(strlen($extra), 4).$extra;

		//save cdr size
		$footer .= $this->packToLittleEndian($this->cdrFilesCount, 4);

		$this->write($footer);

		//save cdr
		$cdrLen = $this->writeCdr();

		//save offset to the start of footer
		$len = $cdrLen+strlen($extra)+13;
		$this->write($this->packToLittleEndian($len, 4));
	}

	private function writeCdr()
	{
		@fclose($this->cdrFileHandle);

		$cdrLen = 0;
		$fp = @fopen($this->filePath.'.cdr', 'rb');

		while (!feof($fp))
		{
			$data = fread($fp, self::CHUNK_SIZE);
			$cdrLen += strlen($data);
			$this->write($data);
		}

		@fclose($fp);
		@unlink($this->filePath.'.cdr');

		return $cdrLen;
	}

	private function clear()
	{
		$this->cdr = array();
		$this->fileOffset = new Math_BigInteger(0);
		$this->cdrFilesCount = 0;
	}

	private function addToCdr($filename, $compressedLength, $uncompressedLength)
	{
		$rec = $this->packToLittleEndian(0, 4); //crc (not used in this version)
		$rec .= $this->packToLittleEndian(strlen($filename), 2);
		$rec .= $filename;
		$rec .= $this->packToLittleEndian($this->fileOffset);
		$rec .= $this->packToLittleEndian($compressedLength);
		$rec .= $this->packToLittleEndian($uncompressedLength); //uncompressed size (not used in this version)

		fwrite($this->cdrFileHandle, $rec);
		fflush($this->cdrFileHandle);

		$this->cdrFilesCount++;
	}

	private function write($data)
	{
		$ff = fwrite($this->fileHandle, $data);
		fflush($this->fileHandle);
	}

	private function read($length)
	{
		return fread($this->fileHandle, $length);
	}

	private function packToLittleEndian($value, $size = 4)
	{
		if (is_int($value))
		{
			$size *= 2; //2 characters for each byte
			$value = str_pad(dechex($value), $size, '0', STR_PAD_LEFT);
			return strrev(pack('H'.$size, $value));
		}

		$hex = str_pad($value->toHex(), 16, '0', STR_PAD_LEFT);

		$high = substr($hex, 0, 8);
		$low  = substr($hex, 8, 8);

		$high = strrev(pack('H8', $high));
		$low = strrev(pack('H8', $low));

		return $low.$high;
	}

	public function extractTo($destinationPath)
	{
		//read offset
		fseek($this->fileHandle, -4, SEEK_END);
		$offset = hexdec($this->unpackLittleEndian($this->read(4), 4));

		//read version
		fseek($this->fileHandle, -$offset, SEEK_END);
		$version = hexdec($this->unpackLittleEndian($this->read(1), 1));

		if ($version != self::VERSION)
		{
			throw new SGExceptionBadRequest('Invalid SGArchive file');
		}

		//read extra size
		$extraSize = hexdec($this->unpackLittleEndian($this->read(4), 4));
		$this->zoffsets = explode(',',$this->read($extraSize));

		//read cdr size
		$cdrSize = hexdec($this->unpackLittleEndian($this->read(4), 4));

		$this->delegate->didCountFilesInsideArchive($cdrSize);

		$this->extractCdr($cdrSize, $destinationPath);
		$this->extractFiles($destinationPath);
	}

	private function extractCdr($cdrSize, $destinationPath)
	{
		while ($cdrSize)
		{
			//read crc (not used in this version)
			$this->read(4);

			//read filename
			$filenameLen = hexdec($this->unpackLittleEndian($this->read(2), 2));
			$filename = $this->read($filenameLen);
			$filename = $this->delegate->getCorrectCdrFilename($filename);

			//read file offset (not used in this version)
			$this->read(8);

			//read compressed length
			$zlen = $this->unpackLittleEndian($this->read(8), 8);
			$zlen = new Math_BigInteger($zlen, 16);

			//read uncompressed length (not used in this version)
			$this->read(8);

			$cdrSize--;

			$path = $destinationPath.$filename;
			$path = str_replace('\\', '/', $path);

			if ($path[strlen($path)-1] != '/') //it's not an empty directory
			{
				$path = dirname($path);
			}

			if (!$this->createPath($path))
			{
				$this->delegate->didFindExtractError('Could not create directory: '.dirname($path));
				continue;
			}

			$this->cdr[] = array($filename, $zlen);
		}
	}

	private function extractFiles($destinationPath)
	{
		$zero = new Math_BigInteger(0);
		$blockSize = new Math_BigInteger(self::CHUNK_SIZE);
		$inflate = sgfun('zlib.inflate');

		fseek($this->fileHandle, 0, SEEK_SET);

		$i = 0;
		$getNewOffset = true;
		$offsetAvailable = true;
		$offset = null;
		$fp = null;

		foreach ($this->cdr as $row)
		{
			$path = $destinationPath.$row[0];
			if (!is_writable(dirname($path)))
			{
				$this->delegate->didFindExtractError('Destination path is not writable: '.dirname($path));
			}

			$zlen = $row[1];

			$rl = self::CHUNK_SIZE;

			if ($zlen->compare(new Math_BigInteger($rl))<=0)
			{
				$rl = (int)$zlen->toString();
			}

			if ($offsetAvailable && $offset && $offset->compare(new Math_BigInteger($rl))<=0)
			{
				$rl = (int)$offset->toString();
				$data = $this->read($rl);
				$data = $inflate($data).$inflate();
				if (is_resource($fp) && strlen($data))
				{
					fwrite($fp, $data);
					fflush($fp);
				}
				$inflate = sgfun('zlib.inflate');
				$getNewOffset = true;
			}

			$fp = @fopen($path, 'wb');

			while ($zlen->compare($zero)>0)
			{
				if ($getNewOffset && $offsetAvailable)
				{
					$offsetAvailable = isset($this->zoffsets[$i]);
					if ($offsetAvailable)
					{
						$offset = $this->zoffsets[$i];
						$offset = new Math_BigInteger($offset);
						$i++;
					}
				}

				$readlen = self::CHUNK_SIZE;
				$close = false;
				$getNewOffset = false;

				if ($zlen->compare(new Math_BigInteger($readlen))<=0)
				{
					$readlen = (int)$zlen->toString();
					$close = true;
				}

				if ($offsetAvailable && $offset->compare(new Math_BigInteger($readlen))<=0)
				{
					$readlen = (int)$offset->toString();
					$close = true;
					$getNewOffset = true;
				}

				if ($readlen)
				{
					$data = $this->read($readlen);
					$data = $inflate($data);
					if (is_resource($fp))
					{
						fwrite($fp, $data);
						fflush($fp);
					}
				}

				if ($close)
				{
					$data = $inflate();
					if (is_resource($fp))
					{
						fwrite($fp, $data);
						fflush($fp);
					}
					$inflate = sgfun('zlib.inflate');
				}

				$zlen = $zlen->subtract(new Math_BigInteger($readlen));
				if ($offsetAvailable)
				{
					$offset = $offset->subtract(new Math_BigInteger($readlen));
				}
			}

			if (is_resource($fp))
			{
				fclose($fp);
			}

			$this->delegate->didExtractFile($path);
		}

		fclose($this->fileHandle);
	}

	private function unpackLittleEndian($data, $size)
	{
		$size *= 2; //2 characters for each byte

		$data = unpack('H'.$size, strrev($data));
		return $data[1];
	}

	private function createPath($path)
	{
		if (is_dir($path)) return true;
		$prev_path = substr($path, 0, strrpos($path, '/', -2) + 1);
		$return = $this->createPath($prev_path);
		if ($return && is_writable($prev_path))
		{
			if (!@mkdir($path)) return false;

			@chmod($path, 0777);
			return true;
		}

		return false;
	}
}
