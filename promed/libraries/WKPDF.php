<?php

        $GLOBALS['WKPDF_BASE_SITE']=LOCALPHP;
		$GLOBALS['WKPDF_CMD']='C:\wkhtmltopdf\wkhtmltopdf.exe';

        /**
         * @author Christian Sciberras
         * @see <a href="http://code.google.com/p/wkhtmltopdf/">http://code.google.com/p/wkhtmltopdf/</a>
         * @copyright 2010 Christian Sciberras / Covac Software.
         * @license LGPL
         * @example
         *   <font color="#008800"><i>//-- Create sample PDF and embed in browser. --//</i></font><br>
         *   <br>
         *   <font color="#008800"><i>// Include WKPDF class.</i></font><br>
         *   <font color="#0000FF">require_once</font>(<font color="#FF0000">'wkhtmltopdf/wkhtmltopdf.php'</font>);<br>
         *   <font color="#008800"><i>// Create PDF object.</i></font><br>
         *   <font color="#EE00EE">$pdf</font>=new <b>WKPDF</b>();<br>
         *   <font color="#008800"><i>// Set PDF's HTML</i></font><br>
         *   <font color="#EE00EE">$pdf</font>-><font color="#0000FF">set_html</font>(<font color="#FF0000">'Hello &lt;b&gt;Mars&lt;/b&gt;!'</font>);<br>
         *   <font color="#008800"><i>// Convert HTML to PDF</i></font><br>
         *   <font color="#EE00EE">$pdf</font>-><font color="#0000FF">render</font>();<br>
         *   <font color="#008800"><i>// Output PDF. The file name is suggested to the browser.</i></font><br>
         *   <font color="#EE00EE">$pdf</font>-><font color="#0000FF">output</font>(<b>WKPDF</b>::<font color="#EE00EE">$PDF_EMBEDDED</font>,<font color="#FF0000">'sample.pdf'</font>);<br>
         * @version
         *   0.0 Chris - Created class.<br>
         *   0.1 Chris - Variable paths fixes.<br>
         *   0.2 Chris - Better error handlng (via exceptions).<br>
         * <font color="#FF0000"><b>IMPORTANT: Make sure that there is a folder in %LIBRARY_PATH%/tmp that is writable!</b></font>
         * <br><br>
         * <b>Features/Bugs/Contact</b><br>
         * Found a bug? Want a modification? Contact me at <a href="mailto:uuf6429@gmail.com">uuf6429@gmail.com</a> or <a href="mailto:contact@covac-software.com">contact@covac-software.com</a>...
         *   guaranteed to get a reply within 2 hours at most (daytime GMT+1).
         */
        class WKPDF {
                /**
                 * Private use variables.
                 */
                private $html='';
                private $cmd = '';
                private $tmp='';
                private $pdf='';
                private $status='';
                private $orient='Landscape';
				private $margin_bottom	= 10;
				private $margin_left	= 10;
				private $margin_right	= 10;
				private $margin_top		= 10;
                private $size='A4';
                private $toc=false;
                private $copies=1;
                private $grayscale=false;
                private $title='';
                private static $cpu='';
                /**
                 * Advanced execution routine.
                 * @param string $cmd The command to execute.
                 * @param string $input Any input not in arguments.
                 * @return array An array of execution data; stdout, stderr and return "error" code.
                 */
                private static function _pipeExec($cmd,$input=''){
                        $proc=proc_open($cmd,array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')),$pipes);
                        fwrite($pipes[0],$input);
                        fclose($pipes[0]);
                        $stdout=stream_get_contents($pipes[1]);
                        fclose($pipes[1]);
                        $stderr=stream_get_contents($pipes[2]);
                        fclose($pipes[2]);
                        $rtn=proc_close($proc);
                        return array(
                                        'stdout'=>$stdout,
                                        'stderr'=>$stderr,
                                        'return'=>$rtn
                                );
                }
                /**
                 * Force the client to download PDF file when finish() is called.
                 */
                public static $PDF_DOWNLOAD='D';
                /**
                 * Returns the PDF file as a string when finish() is called.
                 */
                public static $PDF_ASSTRING='S';
                /**
                 * When possible, force the client to embed PDF file when finish() is called.
                 */
                public static $PDF_EMBEDDED='I';
                /**
                 * PDF file is saved into the server space when finish() is called. The path is returned.
                 */
                public static $PDF_SAVEFILE='F';
                /**
                 * PDF generated as landscape (vertical).
                 */
                public static $PDF_PORTRAIT='Portrait';
                /**
                 * PDF generated as landscape (horizontal).
                 */
                public static $PDF_LANDSCAPE='Landscape';
                /**
                 * Constructor: initialize command line and reserve temporary file.
                 */
                public function __construct() {
						$this->cmd = $GLOBALS['WKPDF_CMD'];
                        if(!file_exists($this->cmd))throw new Exception('WKPDF static executable "'.htmlspecialchars($this->cmd,ENT_QUOTES).'" was not found.');
                }
                /**
                 * Set orientation, use constants from this class.
                 * By default orientation is portrait.
                 * @param string $mode Use constants from this class.
                 */
                public function set_orientation($mode){
                        $this->orient=$mode;
                }
                /**
                 * Set page/paper size.
                 * By default page size is A4.
                 * @param string $size Formal paper size (eg; A4, letter...)
                 */
                public function set_page_size($size){
                        $this->size=$size;
                }
                /**
                 * Whether to automatically generate a TOC (table of contents) or not.
                 * By default TOC is disabled.
                 * @param boolean $enabled True use TOC, false disable TOC.
                 */
                public function set_toc($enabled){
                        $this->toc=$enabled;
                }
                /**
                 * Set the number of copies to be printed.
                 * By default it is one.
                 * @param integer $count Number of page copies.
                 */
                public function set_copies($count){
                        $this->copies=$count;
                }
                /**
                 * Whether to print in grayscale or not.
                 * By default it is OFF.
                 * @param boolean True to print in grayscale, false in full color.
                 */
                public function set_grayscale($mode){
                        $this->grayscale=$mode;
                }
                /**
                 * Set PDF title. If empty, HTML <title> of first document is used.
                 * By default it is empty.
                 * @param string Title text.
                 */
                public function set_title($text){
                        $this->title=$text;
                }
                /**
                 * Set tmp parameter
                 * @param string $tmp
                 */
                public function set_tmp($tmp){
                        $this->tmp=$tmp;
                }
                /**
                 * Set html content.
                 * @param string $html New html content. It *replaces* any previous content.
                 */
                public function set_html($html){
                        $this->html=$html;
                        file_put_contents($this->tmp,$html);
                }
                /**
                 * Returns WKPDF print status.
                 * @return string WPDF print status.
                 */
                public function get_status(){
                        return $this->status;
                }
                /**
                 * Set PDF margins.
                 * @param margins
                 */
                public function set_margins($b,$l,$r,$t)
				{
					$this->margin_bottom = $b;
					$this->margin_left = $l;
					$this->margin_right = $r;
					$this->margin_top = $t;
                }
                /**
                 * Convert HTML to PDF.
                 */
                public function render(){
                        $web=$GLOBALS['WKPDF_BASE_SITE'].$this->tmp;
                        $this->pdf=self::_pipeExec(
                                ''.$this->cmd.''
                                .' -O '.$this->orient                                        			// orientation
								.' -B '.$this->margin_bottom
								.' -L '.$this->margin_left
								.' -R '.$this->margin_right
								.' -T '.$this->margin_top
                                .' --page-size '.$this->size                                            // page size
                                .' "'.$web.'" -'                                                        // URL and optional to write to STDOUT
                        );
                        if(strpos(strtolower($this->pdf['stderr']),'error')!==false)throw new Exception('WKPDF system error: <pre>'.$this->pdf['stderr'].'</pre>');
                        if($this->pdf['stdout']=='')throw new Exception('WKPDF didn\'t return any data. <pre>'.$this->pdf['stderr'].'</pre>');
                        if(((int)$this->pdf['return'])>1)throw new Exception('WKPDF shell error, return code '.(int)$this->pdf['return'].'.');
                        $this->status=$this->pdf['stderr'];
                        $this->pdf=$this->pdf['stdout'];
                        unlink($this->tmp);
                }
                /**
                 * Return PDF with various options.
                 * @param string $mode How two output (constants from this same class).
                 * @param string $file The PDF's filename (the usage depends on $mode.
                 * @return string|boolean Depending on $mode, this may be success (boolean) or PDF (string).
                 */
                public function output($mode,$file){
                        switch($mode){
                                case self::$PDF_DOWNLOAD:
                                        if(!headers_sent()){
                                                header('Content-Description: File Transfer');
                                                header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                                                header('Pragma: public');
                                                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                                                header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                                                // force download dialog
                                                header('Content-Type: application/force-download');
                                                header('Content-Type: application/octet-stream', false);
                                                header('Content-Type: application/download', false);
                                                header('Content-Type: application/pdf', false);
                                                // use the Content-Disposition header to supply a recommended filename
                                                header('Content-Disposition: attachment; filename="'.basename($file).'";');
                                                header('Content-Transfer-Encoding: binary');
                                                header('Content-Length: '.strlen($this->pdf));
                                                echo $this->pdf;
                                        }else{
                                                throw new Exception('WKPDF download headers were already sent.');
                                        }
                                        break;
                                case self::$PDF_ASSTRING:
                                        return $this->pdf;
                                        break;
                                case self::$PDF_EMBEDDED:
                                        if(!headers_sent()){
                                                header('Content-Type: application/pdf');
                                                header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
                                                header('Pragma: public');
                                                header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
                                                header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                                                header('Content-Length: '.strlen($this->pdf));
                                                header('Content-Disposition: inline; filename="'.basename($file).'";');
                                                echo $this->pdf;
                                        }else{
                                                throw new Exception('WKPDF embed headers were already sent.');
                                        }
                                        break;
                                case self::$PDF_SAVEFILE:
                                        return file_put_contents($file,$this->pdf);
                                        break;
                                default:
                                        throw new Exception('WKPDF invalid mode "'.htmlspecialchars($mode,ENT_QUOTES).'".');
                        }
                        return false;
                }
        }
?>