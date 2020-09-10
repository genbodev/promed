<?php
//
// xferSyntax.php
//
// Dicom Transfer Syntax Definitions
//
// CopyRight (c) 2004-2011 RainbowFish Software
//

// non-default transfer syntax table: uid => (explicit vr, big endian, name)
$XFER_SYNTAX_TBL = array (
    "1.2.840.10008.1.2"         => array (false, false, "Implicit VR, Little Endian (default)"),
    "1.2.840.10008.1.2.1"       => array (true, false, "Explicit VR, Little Endian"),
    "1.2.840.10008.1.2.2"       => array (true, true, "Explicit VR, Big Endian"),
    "1.2.840.10008.1.2.5"       => array (true, false, "RLE Lossless"),
    "1.2.840.10008.1.2.4.50"    => array (true, false, "Explicit VR, JPEG Baseline (Process 1)"),
    "1.2.840.10008.1.2.4.51"    => array (true, false, "Explicit VR, JPEG Extended (Process 2 & 4)"),
    "1.2.840.10008.1.2.4.57"    => array (true, false, "Explicit VR, JPEG Lossless (Process 14)"),
    "1.2.840.10008.1.2.4.70"    => array (true, false, "Explicit VR, JPEG Lossless, Non-hierarchical, First-order prediction (Process 14)"),
    "1.2.840.10008.1.2.4.80"      => array (true, false, "JPEG-LS Compression, Lossless Mode"),
    "1.2.840.10008.1.2.4.81"      => array (true, false, "JPEG-LS Compression, Near-Lossless Mode"),
    "1.2.840.10008.1.2.4.90"      => array (true, false, "JPEG 2000 Compression, Lossless Mode"),
    "1.2.840.10008.1.2.4.91"      => array (true, false, "JPEG 2000 Compression, Lossy Mode"),
    "1.2.840.10008.1.2.4.92"    => array (true, false, "JPEG 2000 Lossless Mode Part 2"),
    "1.2.840.10008.1.2.4.93"    => array (true, false, "JPEG 2000 Lossy Mode Part 2"),
    "1.2.840.10008.1.2.4.94"    => array (true, false, "JPIP REFERENCED"),
    "1.2.840.10008.1.2.4.95"    => array (true, false, "JPIP REFERENCED DEFLATE"),
    "1.2.840.10008.1.2.1.99"    => array (true, false, "Explicit VR, Deflated Little Endian"),
    "1.2.840.10008.1.2.4.100"   => array (true, false, "MPEG-2 Compression"),
    "1.2.840.10008.1.2.4.103"   => array (true, false, "MPEG-4 Compression"),
);