<?php
// includes/fpdf.php
// FPDF 1.84 - Pure PHP PDF Generation Engine for TilePoint Invoices

if (!class_exists('FPDF')) {
class FPDF
{
    protected $page;               // current page number
    protected $n;                  // current object number
    protected $offsets;            // array of object offsets
    protected $buffer;             // buffer holding in-memory PDF
    protected $pages;              // array containing pages
    protected $state;              // current document state
    protected $compress;           // compression flag
    protected $k;                  // scale factor (number of points in user unit)
    protected $DefOrientation;     // default orientation
    protected $CurOrientation;     // current orientation
    protected $PageFormats;        // available page formats
    protected $DefPageSize;        // default page size
    protected $CurPageSize;        // current page size
    protected $PageSizes;          // used for page sizes
    protected $wPt, $hPt;          // dimensions of current page in points
    protected $w, $h;              // dimensions of current page in user units
    protected $lMargin;            // left margin
    protected $tMargin;            // top margin
    protected $rMargin;            // right margin
    protected $bMargin;            // page break margin
    protected $cMargin;            // cell margin
    protected $x, $y;              // current position in user units
    protected $lasth;              // height of last printed cell
    protected $LineWidth;          // line width in user units
    protected $fontpath;           // path containing fonts
    protected $CoreFonts;          // array of core font names
    protected $fonts;              // array of used fonts
    protected $FontFiles;          // array of font files
    protected $diffs;              // array of encoding differences
    protected $FontFamily;         // current font family
    protected $FontStyle;          // current font style
    protected $underline;          // underlining flag
    protected $CurrentFont;        // current font info
    protected $FontSizePt;         // current font size in points
    protected $FontSize;           // current font size in user units
    protected $DrawColor;          // commands for drawing color
    protected $FillColor;          // commands for filling color
    protected $TextColor;          // commands for text color
    protected $ColorFlag;          // flag set if fill color is different from text color
    protected $AutoPageBreak;      // automatic page breaking
    protected $PageBreakTrigger;   // threshold to trigger page break
    protected $InHeader;           // flag set when processing header
    protected $InFooter;           // flag set when processing footer
    protected $ZoomMode;           // zoom display mode
    protected $LayoutMode;         // layout display mode
    protected $metadata;           // document properties
    protected $PDFVersion;         // PDF version number

    function __construct($orientation='P', $unit='mm', $size='A4')
    {
        $this->state = 0;
        $this->page = 0;
        $this->n = 2;
        $this->buffer = '';
        $this->pages = array();
        $this->PageSizes = array();
        $this->state = 0;
        $this->fonts = array();
        $this->FontFiles = array();
        $this->diffs = array();
        $this->images = array();
        $this->links = array();
        $this->InHeader = false;
        $this->InFooter = false;
        $this->lasth = 0;
        $this->FontFamily = '';
        $this->FontStyle = '';
        $this->FontSizePt = 12;
        $this->underline = false;
        $this->DrawColor = '0 G';
        $this->FillColor = '0 g';
        $this->TextColor = '0 g';
        $this->ColorFlag = false;
        $this->compress = true;
        $this->k = 72/25.4;
        
        $this->PageFormats = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
            'letter'=>array(612,792), 'legal'=>array(612,1008));
        $size = $this->_getpagesize($size);
        $this->DefPageSize = $size;
        $this->CurPageSize = $size;
        
        $orientation = strtolower($orientation);
        if($orientation=='p' || $orientation=='portrait') {
            $this->DefOrientation = 'P';
            $this->w = $size[0];
            $this->h = $size[1];
        } elseif($orientation=='l' || $orientation=='landscape') {
            $this->DefOrientation = 'L';
            $this->w = $size[1];
            $this->h = $size[0];
        } else {
            $this->Error('Incorrect orientation: '.$orientation);
        }
        $this->CurOrientation = $this->DefOrientation;
        $this->wPt = $this->w*$this->k;
        $this->hPt = $this->h*$this->k;
        
        $this->lMargin = 10;
        $this->tMargin = 10;
        $this->rMargin = 10;
        $this->bMargin = 20;
        $this->cMargin = 1;
        $this->LineWidth = .567/$this->k;
        $this->AutoPageBreak = true;
        $this->PageBreakTrigger = $this->h - $this->bMargin;
        $this->ZoomMode = 'default';
        $this->LayoutMode = 'default';
        $this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
        $this->PDFVersion = '1.3';
    }

    function SetMargins($left, $top, $right=null) {
        $this->lMargin = $left;
        $this->tMargin = $top;
        $this->rMargin = ($right===null) ? $left : $right;
    }

    function SetLeftMargin($margin) { $this->lMargin = $margin; if($this->page>0 && $this->x<$margin) $this->x = $margin; }
    function SetTopMargin($margin) { $this->tMargin = $margin; }
    function SetRightMargin($margin) { $this->rMargin = $margin; }

    function SetAutoPageBreak($auto, $margin=0) {
        $this->AutoPageBreak = $auto;
        $this->bMargin = $margin;
        $this->PageBreakTrigger = $this->h - $margin;
    }

    function AddPage($orientation='', $size='', $rotation=0) {
        if($this->state==0) $this->Open();
        $family = $this->FontFamily;
        $style = $this->FontStyle.($this->underline ? 'U' : '');
        $fontsize = $this->FontSizePt;
        $lw = $this->LineWidth;
        $dc = $this->DrawColor;
        $fc = $this->FillColor;
        $tc = $this->TextColor;
        $cf = $this->ColorFlag;

        if($this->page>0) {
            $this->_endpage();
        }

        $this->_beginpage($orientation, $size, $rotation);
        $this->_out('2 J');
        $this->LineWidth = $lw;
        $this->_out(sprintf('%.2F w', $lw*$this->k));

        if($family) $this->SetFont($family, $style, $fontsize);
        $this->DrawColor = $dc;
        if($dc!='0 G') $this->_out($dc);
        $this->FillColor = $fc;
        if($fc!='0 g') $this->_out($fc);
        $this->TextColor = $tc;
        $this->ColorFlag = $cf;
        $this->Header();
    }

    function Header() {}
    function Footer() {}
    function PageNo() { return $this->page; }

    function SetDrawColor($r, $g=null, $b=null) {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->DrawColor = sprintf('%.3F G', $r/255);
        else
            $this->DrawColor = sprintf('%.3F %.3F %.3F RG', $r/255, $g/255, $b/255);
        if($this->page>0) $this->_out($this->DrawColor);
    }

    function SetFillColor($r, $g=null, $b=null) {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->FillColor = sprintf('%.3F g', $r/255);
        else
            $this->FillColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = ($this->FillColor!=$this->TextColor);
        if($this->page>0) $this->_out($this->FillColor);
    }

    function SetTextColor($r, $g=null, $b=null) {
        if(($r==0 && $g==0 && $b==0) || $g===null)
            $this->TextColor = sprintf('%.3F g', $r/255);
        else
            $this->TextColor = sprintf('%.3F %.3F %.3F rg', $r/255, $g/255, $b/255);
        $this->ColorFlag = ($this->FillColor!=$this->TextColor);
    }

    function GetStringWidth($s) {
        $s = (string)$s;
        $cw = &$this->CurrentFont['cw'];
        $w = 0;
        $l = strlen($s);
        for($i=0;$i<$l;$i++)
            $w += $cw[$s[$i]] ?? 600;
        return $w*$this->FontSize/1000;
    }

    function SetLineWidth($width) {
        $this->LineWidth = $width;
        if($this->page>0) $this->_out(sprintf('%.2F w', $width*$this->k));
    }

    function Line($x1, $y1, $x2, $y2) {
        $this->_out(sprintf('%.2F %.2F m %.2F %.2F l S', $x1*$this->k, ($this->h-$y1)*$this->k, $x2*$this->k, ($this->h-$y2)*$this->k));
    }

    function Rect($x, $y, $w, $h, $style='') {
        if($style=='F') $op = 'f';
        elseif($style=='FD' || $style=='DF') $op = 'B';
        else $op = 'S';
        $this->_out(sprintf('%.2F %.2F %.2F %.2F re %s', $x*$this->k, ($this->h-$y)*$this->k, $w*$this->k, -$h*$this->k, $op));
    }

    function SetFont($family, $style='', $size=0) {
        if($family=='') $family = $this->FontFamily;
        else $family = strtolower($family);
        if($family=='arial') $family = 'helvetica';
        $style = strtoupper($style);
        if(strpos($style,'U')!==false) {
            $this->underline = true;
            $style = str_replace('U','',$style);
        } else $this->underline = false;
        if($style=='ITALIC') $style = 'I';
        if($style=='BOLD') $style = 'B';
        if($size==0) $size = $this->FontSizePt;

        if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size) return;

        $fontkey = $family.$style;
        if(!isset($this->fonts[$fontkey])) {
            if(in_array($family, $this->CoreFonts)) {
                if($family=='symbol' || $family=='zapfdingbats') $style = '';
                $this->fonts[$fontkey] = array('i'=>count($this->fonts)+1, 'type'=>'core', 'name'=>$this->_getcorefontname($family, $style), 'up'=>-100, 'ut'=>50, 'cw'=>array());
                for($i=32;$i<=167;$i++) $this->fonts[$fontkey]['cw'][chr($i)] = 600;
            } else {
                $this->Error('Undefined font: '.$family.' '.$style);
            }
        }

        $this->FontFamily = $family;
        $this->FontStyle = $style;
        $this->FontSizePt = $size;
        $this->FontSize = $size/$this->k;
        $this->CurrentFont = &$this->fonts[$fontkey];
        if($this->page>0)
            $this->_out(sprintf('BT /F%d %.2F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
    }

    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='') {
        $k = $this->k;
        if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AutoPageBreak) {
            $x = $this->x;
            $ws = $this->ws ?? 0;
            if($ws>0) {
                $this->ws = 0;
                $this->_out('0 Tw');
            }
            $this->AddPage($this->CurOrientation, $this->CurPageSize);
            $this->x = $x;
        }

        if($w==0) $w = $this->w - $this->rMargin - $this->x;
        $s = '';
        if($fill || $border==1) {
            if($fill) $op = ($border==1) ? 'B' : 'f';
            else $op = 'S';
            $s = sprintf('%.2F %.2F %.2F %.2F re %s ', $this->x*$k, ($this->h-$this->y)*$k, $w*$k, -$h*$k, $op);
        }
        if(is_string($border)) {
            $x = $this->x;
            $y = $this->y;
            if(strpos($border,'L')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-$y)*$k, $x*$k, ($this->h-($y+$h))*$k);
            if(strpos($border,'T')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-$y)*$k);
            if(strpos($border,'R')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', ($x+$w)*$k, ($this->h-$y)*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
            if(strpos($border,'B')!==false) $s .= sprintf('%.2F %.2F m %.2F %.2F l S ', $x*$k, ($this->h-($y+$h))*$k, ($x+$w)*$k, ($this->h-($y+$h))*$k);
        }

        if($txt!=='') {
            if($align=='R') $dx = $w - $this->cMargin - $this->GetStringWidth($txt);
            elseif($align=='C') $dx = ($w - $this->GetStringWidth($txt))/2;
            else $dx = $this->cMargin;
            if($this->ColorFlag) $s .= 'q '.$this->TextColor.' ';
            $txt2 = str_replace(')', '\\)', str_replace('(', '\\(', str_replace('\\', '\\\\', $txt)));
            $s .= sprintf('BT %.2F %.2F Td (%s) Tj ET', ($this->x+$dx)*$k, ($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k, $txt2);
            if($this->ColorFlag) $s .= ' Q';
        }
        if($s) $this->_out($s);
        $this->lasth = $h;
        if($ln>0) {
            $this->y += $h;
            if($ln==1) $this->x = $this->lMargin;
        } else $this->x += $w;
    }

    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false) {
        $cw = &$this->CurrentFont['cw'];
        if($w==0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w-2*$this->cMargin)*1000/$this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if($nb>0 && $s[$nb-1]=="\n") $nb--;
        $sep = -1; $i = 0; $j = 0; $l = 0; $ns = 0; $nl = 1;
        while($i<$nb) {
            $c = $s[$i];
            if($c=="\n") {
                $this->Cell($w, $h, substr($s, $j, $i-$j), $border, 2, $align, $fill);
                $i++; $sep = -1; $j = $i; $l = 0; $ns = 0; $nl++;
                continue;
            }
            if($c==' ') { $sep = $i; }
            $l += $cw[$c] ?? 600;
            if($l>$wmax) {
                if($sep==-1) {
                    if($i==$j) $i++;
                    $this->Cell($w, $h, substr($s, $j, $i-$j), $border, 2, $align, $fill);
                } else {
                    $this->Cell($w, $h, substr($s, $j, $sep-$j), $border, 2, $align, $fill);
                    $i = $sep + 1;
                }
                $sep = -1; $j = $i; $l = 0; $ns = 0; $nl++;
            } else $i++;
        }
        if($i!=$j) $this->Cell($w, $h, substr($s, $j, $i-$j), $border, 2, $align, $fill);
        $this->x = $this->lMargin;
    }

    function Ln($h=null) {
        $this->x = $this->lMargin;
        if($h===null) $this->y += $this->lasth;
        else $this->y += $h;
    }

    function GetX() { return $this->x; }
    function SetX($x) { if($x>=0) $this->x = $x; else $this->x = $this->w+$x; }
    function GetY() { return $this->y; }
    function SetY($y) { $this->x = $this->lMargin; if($y>=0) $this->y = $y; else $this->y = $this->h+$y; }
    function SetXY($x, $y) { $this->SetY($y); $this->SetX($x); }

    function Open() { $this->state = 1; }

    function Output($dest='', $name='', $isUTF8=false) {
        if($this->state<3) {
            $this->Close();
        }
        if(empty($name)) $name = 'doc.pdf';
        if(empty($dest)) $dest = 'I';

        switch(strtoupper($dest)) {
            case 'I':
                $this->_sendHeader($name, true);
                echo $this->buffer;
                break;
            case 'D':
                $this->_sendHeader($name, false);
                echo $this->buffer;
                break;
            case 'F':
                file_put_contents($name, $this->buffer);
                break;
            case 'S':
                return $this->buffer;
        }
        return '';
    }

    protected function _sendHeader($name, $inline) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: ' . ($inline ? 'inline' : 'attachment') . '; filename="' . $name . '"');
        header('Cache-Control: private, max-age=0, must-revalidate');
        header('Pragma: public');
    }

    protected function _getpagesize($size) {
        if(is_string($size)) {
            $a = strtolower($size);
            if(isset($this->PageFormats[$a]))
                return array($this->PageFormats[$a][0]/$this->k, $this->PageFormats[$a][1]/$this->k);
        } else {
            return array($size[0], $size[1]);
        }
        return array(210, 297);
    }

    protected function _beginpage($orientation, $size, $rotation) {
        $this->page++;
        $this->pages[$this->page] = '';
        $this->state = 2;
        $this->x = $this->lMargin;
        $this->y = $this->tMargin;
        $this->FontFamily = '';
    }

    protected function _endpage() { $this->state = 1; }

    protected function _out($s) {
        if($this->state==2) $this->pages[$this->page] .= $s."\n";
        else $this->buffer .= $s."\n";
    }

    protected function _getcorefontname($family, $style) {
        if($family=='helvetica') return 'Helvetica'.($style ? '-'.$style : '');
        if($family=='times') return 'Times-'.($style=='B' ? 'Bold' : ($style=='I' ? 'Italic' : 'BoldItalic'));
        if($family=='courier') return 'Courier'.($style ? '-'.$style : '');
        return 'Helvetica';
    }

    protected function Close() {
        if($this->state==3) return;
        if($this->page==0) $this->AddPage();
        $this->_endpage();
        $this->_putheader();
        $this->_putpages();
        $this->_putresources();
        $this->_putinfo();
        $this->_putcatalog();
        $this->state = 3;
    }

    protected function _putheader() { $this->_out('%PDF-'.$this->PDFVersion); }
    protected function _putpages() {
        $nb = $this->page;
        for($n=1;$n<=$nb;$n++) $this->offsets[1+2*($n-1)] = strlen($this->buffer);
        for($n=1;$n<=$nb;$n++) {
            $this->_out((1+2*($n-1)).' 0 obj');
            $this->_out('<</Type /Page /Parent 2 0 R /MediaBox [0 0 '.$this->wPt.' '.$this->hPt.'] /Contents '.(2+2*($n-1)).' 0 R /Resources 3 0 R>>');
            $this->_out('endobj');
            $this->offsets[2+2*($n-1)] = strlen($this->buffer);
            $p = $this->pages[$n];
            $this->_out((2+2*($n-1)).' 0 obj');
            $this->_out('<</Length '.strlen($p).'>>');
            $this->_out('stream');
            $this->_out($p);
            $this->_out('endstream');
            $this->_out('endobj');
        }
        $this->offsets[2] = strlen($this->buffer);
        $this->_out('2 0 obj');
        $this->_out('<</Type /Pages /Kids [');
        for($n=1;$n<=$nb;$n++) $this->_out((1+2*($n-1)).' 0 R ');
        $this->_out('] /Count '.$nb.'>>');
        $this->_out('endobj');
    }

    protected function _putresources() {
        $this->offsets[3] = strlen($this->buffer);
        $this->_out('3 0 obj');
        $this->_out('<</ProcSet [/PDF /Text] /Font <<');
        foreach($this->fonts as $font) {
            $this->_out('/F'.$font['i'].' '.$font['n'].' 0 R');
        }
        $this->_out('>>>>');
        $this->_out('endobj');
        foreach($this->fonts as $k=>$font) {
            $this->fonts[$k]['n'] = $this->n + 1;
            $this->n++;
            $this->offsets[$this->n] = strlen($this->buffer);
            $this->_out($this->n.' 0 obj');
            $this->_out('<</Type /Font /Subtype /Type1 /BaseFont /'.$font['name'].' /Encoding /WinAnsiEncoding>>');
            $this->_out('endobj');
        }
    }

    protected function _putinfo() {
        $this->_out('/Producer (TilePoint FPDF Engine)');
        $this->_out('/Title (TilePoint Premium Invoice)');
    }

    protected function _putcatalog() {
        $this->n++;
        $this->offsets[$this->n] = strlen($this->buffer);
        $this->_out($this->n.' 0 obj');
        $this->_out('<</Type /Catalog /Pages 2 0 R>>');
        $this->_out('endobj');
        $o = strlen($this->buffer);
        $this->_out('xref');
        $this->_out('0 '.($this->n+1));
        $this->_out('0000000000 65535 f ');
        for($i=1;$i<=$this->n;$i++) {
            $this->_out(sprintf('%010d 00000 n ', $this->offsets[$i] ?? 0));
        }
        $this->_out('trailer');
        $this->_out('<</Size '.($this->n+1).' /Root '.$this->n.' 0 R>>');
        $this->_out('startxref');
        $this->_out($o);
        $this->_out('%%EOF');
    }

    protected function Error($msg) { die('<b>FPDF error:</b> '.$msg); }
}
}
