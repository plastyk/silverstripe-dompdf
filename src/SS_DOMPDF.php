<?php
namespace Burnbright\SS_DOMPDF;

use Dompdf\Dompdf;
use SilverStripe\Assets\File;
use SilverStripe\Assets\FileNameFilter;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\Director;

/**
 * SilverStripe wrapper for DOMPDF
 */
class SS_DOMPDF
{
    protected $dompdf;

    public function __construct()
    {
        // inhibit DOMPDF's auto-loader
        define('DOMPDF_ENABLE_AUTOLOAD', false);

        //set configuration
        $this->dompdf = new Dompdf();
        $this->dompdf->setBasePath(BASE_PATH);
        $this->dompdf->setBaseHost(Director::absoluteBaseURL());
    }

    //
    public function setOption($key, $value)
    {
        $this->dompdf->getOptions()->set($key, $value);
    }

    public function set_paper($size, $orientation)
    {
        $this->dompdf->setPaper($size, $orientation);
    }

    public function setHTML($html)
    {
        $this->dompdf->loadHtml($html);
    }

    public function setHTMLFromFile($filename)
    {
        $this->dompdf->loadHtmlFile($filename);
    }

    public function render()
    {
        $this->dompdf->render();
    }

    public function output($options = null)
    {
        return $this->dompdf->output($options);
    }

    public function stream($outfile, $options = '')
    {
        return $this->dompdf->stream($this->addFileExt($outfile), $options);
    }

    public function toFile($filename = "file", $folder = "PDF")
    {
        $filename = $this->addFileExt($filename);
        $filepath = File::join_paths([$folder, FileNameFilter::create()->filter($filename)]);
        $folder   = Folder::find_or_make($folder);
        $output   = $this->output();
        $file     = new File();
        $file->setFromString($output, $filepath);
        $file->ParentID = $folder->ID;
        $file->write();
        $file->publishFile();
        return $file;
    }

    public function addFileExt($filename, $new_extension = 'pdf')
    {
        if (strpos($filename, "." . $new_extension)) {
            return $filename;
        }
        $info = pathinfo($filename);
        return $info['filename'] . '.' . $new_extension;
    }

    /**
     * uesful function that streams the pdf to the browser,
     * with correct headers, and ends php execution.
     */
    public function streamdebug()
    {
        header('Content-type: application/pdf');
        $this->stream('debug', array('Attachment' => 0));
        die();
    }
}
