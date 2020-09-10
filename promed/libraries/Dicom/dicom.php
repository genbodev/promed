<?php
//
// dicom.php
//
// Module for client-side applications using DICOM 3.0 protocols
//
// CopyRight (c) 2003-2012 RainbowFish Software
//
require_once "sharedData.php";
require_once "xferSyntax.php";
require_once "locale.php";

// constant definitions
// abstract syntax definitions
$C_ECHO = '1.2.840.10008.1.1';
define( 'C_ECHO', $C_ECHO );
$C_FIND = '1.2.840.10008.5.1.4.1.2.1.1';
define( 'C_FIND', $C_FIND );
$C_FIND_STUDYROOT = '1.2.840.10008.5.1.4.1.2.2.1';
define( 'C_FIND_STUDYROOT', $C_FIND_STUDYROOT );
$C_MOVE = '1.2.840.10008.5.1.4.1.2.1.2';
define( 'C_MOVE', $C_MOVE );
$C_MOVE_STUDYROOT = '1.2.840.10008.5.1.4.1.2.2.2';
define( 'C_MOVE_STUDYROOT', $C_MOVE_STUDYROOT );
$WORKLIST_FIND = '1.2.840.10008.5.1.4.31';
define( 'WORKLIST_FIND', $WORKLIST_FIND );
$BASIC_PRINT = '1.2.840.10008.5.1.1.9';
define( 'BASIC_PRINT', $BASIC_PRINT );
// read/write buffer size
$MAX_PDU_LEN = 16 * 1024;
define( 'MAX_PDU_LEN', $MAX_PDU_LEN );
// un-defined length
$UNDEF_LEN = -1;
define( 'UNDEF_LEN', $UNDEF_LEN );

// attribute tag to (name, type) table
$ATTR_TBL = array(
	0x00080005 => new CNameType("Specific Character Set", "A"),
	0x00080008 => new CNameType("Image Type", "A"),
	0x00080012 => new CNameType("Instance Creation Date", "A"),
	0x00080013 => new CNameType("Instance Creation Time", "A"),
	0x00080014 => new CNameType("Instance Creator UID", "a"),
	0x00080016 => new CNameType("SOP Class UID", "a"),
	0x00080018 => new CNameType("SOP Instance UID", "a"),
	0x00080020 => new CNameType("Study Date", "A"),
	0x00080021 => new CNameType("Series Date", "A"),
	0x00080022 => new CNameType("Acquisition Date", "A"),
	0x00080023 => new CNameType("Content Date", "A"),
	0x00080024 => new CNameType("Overlay Date", "A"),
	0x00080025 => new CNameType("Curve Date", "A"),
	0x0008002A => new CNameType("Acquisition Datetime", "A"),
	0x00080030 => new CNameType("Study Time", "A"),
	0x00080031 => new CNameType("Series Time", "A"),
	0x00080032 => new CNameType("Acquisition Time", "A"),
	0x00080033 => new CNameType("Content Time", "A"),
	0x00080034 => new CNameType("Overlay Time", "A"),
	0x00080035 => new CNameType("Curve Time", "A"),
	0x00080040 => new CNameType("Data Set Type (Retired)", "A"),
	0x00080041 => new CNameType("Data Set Subtype (Retired)", "A"),
	0x00080050 => new CNameType("Accession Number", "A"),
	0x00080052 => new CNameType("Query/Retrieve Level", "A"),
    0x00080054 => new CNameType("Retrieve AE Title", "A"),
	0x00080060 => new CNameType("Modality", "A"),
	0x00080070 => new CNameType("Manufacturer", "A"),
	0x00080080 => new CNameType("Institution Name", "A"),
	0x00080081 => new CNameType("Institution Address", "A"),
	0x00080090 => new CNameType("Referring Physician's Name", "A"),
	0x00080100 => new CNameType("Code Value", "A"),
	0x00080102 => new CNameType("Code Scheme Designator", "A"),
	0x00080103 => new CNameType("Code Scheme Version", "A"),
	0x00080104 => new CNameType("Code Meaning", "A"),
	0x0008010c => new CNameType("Coding Scheme UID", "a"),
	0x00081010 => new CNameType("Station Name", "A"),
	0x00081030 => new CNameType("Study Description", "A"),
	0x0008103E => new CNameType("Series Description", "A"),
	0x00081040 => new CNameType("Institution Department Name", "A"),
	0x00081048 => new CNameType("Physician(s) of Record", "A"),
	0x00081050 => new CNameType("Performing Physician's Name", "A"),
	0x00081060 => new CNameType("Name of Physician(s) Reading Study", "A"),
	0x00081070 => new CNameType("Operator's Name", "A"),
	0x00081080 => new CNameType("Admitting Diagnoses Description", "A"),
	0x00081090 => new CNameType("Manufacturer's Model Name", "A"),
	0x00081110 => new CNameType("Referenced Study Sequence", "S"),
	0x00081111 => new CNameType("Referenced Performed Procedure Step Sequence", "S"),
	0x00081115 => new CNameType("Referenced Series Sequence", "S"),
	0x00081120 => new CNameType("Referenced Patient Sequence", "S"),
	0x00081125 => new CNameType("Referenced Visit Sequence", "S"),
	0x00081130 => new CNameType("Referenced Overlay Sequence", "S"),
	0x0008113A => new CNameType("Referenced Waveform Sequence", "S"),
	0x00081140 => new CNameType("Referenced Image Sequence", "S"),
	0x00081145 => new CNameType("Referenced Curve Sequence", "S"),
	0x0008114A => new CNameType("Referenced Instance Sequence", "S"),
	0x00081150 => new CNameType("Referenced SOP Class UID", "a"),
	0x00081155 => new CNameType("Referenced SOP Instance UID", "a"),
	0x00081160 => new CNameType("Referenced Frame Number", "A"),
	0x00081199 => new CNameType("Referenced SOP Sequence", "S"),
	0x00100010 => new CNameType("Patient's Name", "A"),
	0x00100020 => new CNameType("Patient ID", "A"),
	0x00100030 => new CNameType("Patient's Birth Date", "A"),
	0x00100040 => new CNameType("Patient's Sex", "A"),
	0x00101010 => new CNameType("Patient's Age", "A"),
	0x00101020 => new CNameType("Patient's Size", "A"),
	0x00101030 => new CNameType("Patient's Weight", "A"),
	0x00101040 => new CNameType("Patient's Address", "A"),
	0x00101050 => new CNameType("Insurance Plan Identification", "A"),
	0x00101060 => new CNameType("Patient's Mother's Birth Name", "A"),
	0x00101080 => new CNameType("Military Rank", "A"),
	0x00101090 => new CNameType("Medical Record Locator", "A"),
	0x00102000 => new CNameType("Medical Alerts", "A"),
	0x00102110 => new CNameType("Contrast Allergies", "A"),
	0x00102150 => new CNameType("Country of Residence", "A"),
	0x00102152 => new CNameType("Region of Residence", "A"),
	0x00102154 => new CNameType("Patient's Telephone Numbers", "A"),
	0x00102160 => new CNameType("Ethnic Group", "A"),
	0x00102180 => new CNameType("Occupation", "A"),
	0x001021A0 => new CNameType("Smoking Status", "A"),
	0x001021B0 => new CNameType("Additional Patient History", "A"),
	0x00180010 => new CNameType("Contrast/Bolus Agent", "A"),
	0x00180015 => new CNameType("Body Part Examined", "A"),
	0x00180020 => new CNameType("Scanning Sequence", "A"),
	0x00180021 => new CNameType("Sequence Variant", "A"),
	0x00180022 => new CNameType("Scan Options", "A"),
	0x00180023 => new CNameType("MR Acquisition Type", "A"),
	0x00180024 => new CNameType("Sequence Name", "A"),
	0x00180025 => new CNameType("Angio Flag", "A"),
	0x00180030 => new CNameType("Radionuclide", "A"),
	0x00180031 => new CNameType("Radiopharmaceutical", "A"),
	0x00180034 => new CNameType("Intervention Drug Name", "A"),
	0x00180035 => new CNameType("Intervention Drug Start Time", "A"),
	0x00180036 => new CNameType("Intervention Therapy Sequence", "S"),
	0x00180040 => new CNameType("Cine Rate", "A"),
	0x00180050 => new CNameType("Slice Thickness", "A"),
	0x00180060 => new CNameType("KVP", "A"),
	0x00180070 => new CNameType("Counts Accumulated", "A"),
	0x00180071 => new CNameType("Acquisition Termination Condition", "A"),
	0x00180072 => new CNameType("Effective Duration", "A"),
	0x00180073 => new CNameType("Acquisition Start Condition", "A"),
	0x00180074 => new CNameType("Acquisition Start Condition Data", "A"),
	0x00180075 => new CNameType("Acquisition Termination Condition Data", "A"),
	0x00180080 => new CNameType("Repetition Time", "A"),
	0x00180081 => new CNameType("Echo Time", "A"),
	0x00180082 => new CNameType("Inversion Time", "A"),
	0x00180083 => new CNameType("Number of Averages", "A"),
	0x00180084 => new CNameType("Imaging Frequency", "A"),
	0x00180085 => new CNameType("Imaged Nucleus", "A"),
	0x00180086 => new CNameType("Echo Number(s)", "A"),
	0x00180087 => new CNameType("Magnetic Field Strength", "A"),
	0x00180088 => new CNameType("Spacing Between Slices", "A"),
	0x00180089 => new CNameType("Number of Phase Encoding Steps", "A"),
	0x00180090 => new CNameType("Data Collection Diameter", "A"),
	0x00180091 => new CNameType("Echo Train Length", "A"),
	0x00180093 => new CNameType("Percent Sampling", "A"),
	0x00180094 => new CNameType("Percent Phase Field of View", "A"),
	0x00180095 => new CNameType("Pixel Bandwidth", "A"),
	0x00181000 => new CNameType("Device Serial Number", "A"),
	0x00181004 => new CNameType("Plate ID", "A"),
	0x00181010 => new CNameType("Secondary Capture Device ID", "A"),
	0x00181011 => new CNameType("Hardcopy Creation Device ID", "A"),
	0x00181012 => new CNameType("Date of Secondary Capture", "A"),
	0x00181014 => new CNameType("Time of Secondary Capture", "A"),
	0x00181016 => new CNameType("Secondary Capture Device Manufacturer", "A"),
	0x00181017 => new CNameType("Hardcopy Device Manufacturer", "A"),
	0x00181018 => new CNameType("Secondary Capture Device Manufacturer's Model Name", "A"),
	0x00181019 => new CNameType("Secondary Capture Device Software Version(s)", "A"),
	0x0018101A => new CNameType("Hardcopy Device Software Version", "A"),
	0x0018101B => new CNameType("Hardcopy Device Manufacturer's Model Name", "A"),
	0x00181020 => new CNameType("Software Version(s)", "A"),
	0x00181030 => new CNameType("Protocol Name", "A"),
	0x00181040 => new CNameType("Contrast/Bolus Route", "A"),
	0x00181041 => new CNameType("Contrast/Bolus Volume", "A"),
	0x00181042 => new CNameType("Contrast/Bolus Start Time", "A"),
	0x00181043 => new CNameType("Contrast/Bolus Stop Time", "A"),
	0x00181044 => new CNameType("Contrast/Bolus Total Dose", "A"),
	0x00181045 => new CNameType("Syringe Counts", "A"),
	0x00181046 => new CNameType("Contrast Flow Rate(s)", "A"),
	0x00181047 => new CNameType("Contrast Flow Duration(s)", "A"),
	0x00181048 => new CNameType("Contrast/Bolus Ingredient", "A"),
	0x00181049 => new CNameType("Contrast/Bolus Ingredient Concentration", "A"),
	0x00181050 => new CNameType("Spatial Resolution", "A"),
	0x00181060 => new CNameType("Trigger Time", "A"),
	0x00181061 => new CNameType("Trigger Source or Type", "A"),
	0x00181062 => new CNameType("Nominal Interval", "A"),
	0x00181063 => new CNameType("Frame Time", "A"),
	0x00181064 => new CNameType("Frame Type", "A"),
	0x00181065 => new CNameType("Frame Time Vector", "A"),
	0x00181066 => new CNameType("Frame Delay", "A"),
	0x00181067 => new CNameType("Image Trigger Delay", "A"),
	0x00181068 => new CNameType("Multiplex Group Time Offset", "A"),
	0x00181069 => new CNameType("Trigger Time Offset", "A"),
	0x0018106A => new CNameType("Synchronization Trigger", "A"),
	0x0018106C => new CNameType("Synchronization Channel", "v"),
	0x0018106E => new CNameType("Trigger Sample Position", "V"),
	0x00181070 => new CNameType("Radiopharmaceutical Route", "A"),
	0x00181071 => new CNameType("Radiopharmaceutical Volume", "A"),
	0x00181072 => new CNameType("Radiopharmaceutical Start Time", "A"),
	0x00181073 => new CNameType("Radiopharmaceutical Stop Time", "A"),
	0x00181074 => new CNameType("Radionuclide Total Dose", "A"),
	0x00181075 => new CNameType("Radionuclide Half Life", "A"),
	0x00181076 => new CNameType("Radionuclide Position Fraction", "A"),
	0x00181077 => new CNameType("Radiopharmaceutical Specific Activity", "A"),
	0x00181080 => new CNameType("Beat Rejection Flag", "A"),
	0x00181081 => new CNameType("Low R-R Value", "A"),
	0x00181082 => new CNameType("High R-R Value", "A"),
	0x00181083 => new CNameType("Intervals Acquired", "A"),
	0x00181084 => new CNameType("Intervals Rejected", "A"),
	0x00181085 => new CNameType("PVC Rejection", "A"),
	0x00181086 => new CNameType("Skip Beats", "A"),
	0x00181088 => new CNameType("Heart Rate", "A"),
	0x00181090 => new CNameType("Cardiac Number of Images", "A"),
	0x00181094 => new CNameType("Trigger Window", "A"),
	0x00181100 => new CNameType("Reconstruction Diameter", "A"),
	0x00181110 => new CNameType("Distance Source to Detector", "A"),
	0x00181111 => new CNameType("Distance Source to Patient", "A"),
	0x00181114 => new CNameType("Estimated Radiographic Magnification Factor", "A"),
	0x00181120 => new CNameType("Gantry/Detector Tilt", "A"),
	0x00181121 => new CNameType("Gantry/Detector Slew", "A"),
	0x00181130 => new CNameType("Table Height", "A"),
	0x00181131 => new CNameType("Table Traverse", "A"),
	0x00181134 => new CNameType("Table Motion", "A"),
	0x00181135 => new CNameType("Table Vertical Increment", "A"),
	0x00181136 => new CNameType("Table Lateral Increment", "A"),
	0x00181137 => new CNameType("Table Longitudinal Increment", "A"),
	0x00181138 => new CNameType("Table Angle", "A"),
	0x0018113A => new CNameType("Table Type", "A"),
	0x00181140 => new CNameType("Rotation Direction", "A"),
	0x00181141 => new CNameType("Angular Position", "A"),
	0x00181142 => new CNameType("Radial Position", "A"),
	0x00181143 => new CNameType("Scan Arc", "A"),
	0x00181144 => new CNameType("Angular Step", "A"),
	0x00181145 => new CNameType("Center of Rotation Offset", "A"),
	0x00181146 => new CNameType("Rotation Offset", "A"),
	0x00181147 => new CNameType("Field of View Shape", "A"),
	0x00181149 => new CNameType("Field of View Dimension(s)", "A"),
	0x00181150 => new CNameType("Exposure Time", "A"),
	0x00181151 => new CNameType("X-ray Tube Current", "A"),
	0x00181152 => new CNameType("Exposure", "A"),
	0x00181153 => new CNameType("Exposure in uAs", "A"),
	0x00181154 => new CNameType("Average Pulse Width", "A"),
	0x00181155 => new CNameType("Radiation Setting", "A"),
	0x00181156 => new CNameType("Rectification Type", "A"),
	0x0018115A => new CNameType("Radiation Type", "A"),
	0x0018115E => new CNameType("Image Area Dose Product", "A"),
	0x00181160 => new CNameType("Filter Type", "A"),
	0x00181161 => new CNameType("Type of Filters", "A"),
	0x00181162 => new CNameType("Intensifier Size", "A"),
	0x00181164 => new CNameType("Imager Pixel Spacing", "A"),
	0x00181166 => new CNameType("Grid", "A"),
	0x00181170 => new CNameType("Generator Power", "A"),
	0x00181180 => new CNameType("Collimator/grid Name", "A"),
	0x00181181 => new CNameType("Collimator Type", "A"),
	0x00181182 => new CNameType("Focal Distance", "A"),
	0x00181183 => new CNameType("X Focus Center", "A"),
	0x00181184 => new CNameType("Y Focus Center", "A"),
	0x00181190 => new CNameType("Focal Spot(s)", "A"),
	0x00181191 => new CNameType("Anode Target Material", "A"),
	0x001811A0 => new CNameType("Boday Part Thickness", "A"),
	0x001811A2 => new CNameType("Compression Force", "A"),
	0x00181200 => new CNameType("Date of Last Calibration", "A"),
	0x00181201 => new CNameType("Time of Last Calibration", "A"),
	0x00181210 => new CNameType("Convolution Kernel", "A"),
	0x00181240 => new CNameType("Upper/Lower Pixel Values", "A"),
	0x00181242 => new CNameType("Actual Frame Duration", "A"),
	0x00181243 => new CNameType("Count Rate", "A"),
	0x00181244 => new CNameType("Preferred Playback Sequence", "v"),
	0x00181250 => new CNameType("Receive Coil Name", "A"),
	0x00181251 => new CNameType("Transmit Coil Name", "A"),
	0x00181260 => new CNameType("Plate Type", "A"),
	0x00181261 => new CNameType("Phosphor Type", "A"),
	0x00181300 => new CNameType("Scan Velocity", "A"),
	0x00181301 => new CNameType("Whole Body Technique", "A"),
	0x00181302 => new CNameType("Scan Length", "A"),
	0x00181310 => new CNameType("Acquisition Matrix", "v"),
	0x00181312 => new CNameType("In-plane Phase Encoding Direction", "A"),
	0x00181314 => new CNameType("Flip Angle", "A"),
	0x00181315 => new CNameType("Variable Flip Angle Flag", "A"),
	0x00181316 => new CNameType("SAR", "A"),
	0x00181318 => new CNameType("dB/dt", "A"),
	0x00181400 => new CNameType("Acquisition Device Processing Description", "A"),
	0x00185000 => new CNameType("Output Power", "A"),
	0x00185010 => new CNameType("Transducer Data", "A"),
	0x00185012 => new CNameType("Focus Depth", "A"),
	0x00185020 => new CNameType("Processing Function", "A"),
	0x00185021 => new CNameType("Postprocessing Function", "A"),
	0x00185050 => new CNameType("Depth of Scan Field", "A"),
	0x00185100 => new CNameType("Patient Position", "A"),
	0x00185101 => new CNameType("View Position", "A"),
	0x0020000d => new CNameType("Study Instance UID", "a"),
	0x0020000e => new CNameType("Series Instance UID", "a"),
	0x00200010 => new CNameType("Study ID", "A"),
	0x00200011 => new CNameType("Series Number", "A"),
	0x00200012 => new CNameType("Acquisition Number", "A"),
	0x00200013 => new CNameType("Instance Number", "A"),
	0x00200020 => new CNameType("Patient Orientation", "A"),
	0x00200022 => new CNameType("Overlay Number", "A"),
	0x00200024 => new CNameType("Curve Number", "A"),
	0x00200026 => new CNameType("Lookup Table Number", "A"),
	0x00200030 => new CNameType("Image Position", "A"),
	0x00200032 => new CNameType("Image Position(Patient)", "A"),
	0x00200035 => new CNameType("Image Orientation", "A"),
	0x00200037 => new CNameType("Image Orientation(Patient)", "A"),
	0x00200050 => new CNameType("Location", "A"),
	0x00200052 => new CNameType("Frame of Reference UID", "a"),
	0x00200060 => new CNameType("Laterality", "A"),
	0x00200062 => new CNameType("Image Laterality", "A"),
	0x00200100 => new CNameType("Temporal Position Identifier", "A"),
	0x00200105 => new CNameType("Number of Temporal Positions", "A"),
	0x00200110 => new CNameType("Temporal Resolution", "A"),
	0x00200200 => new CNameType("Synchronization Frame of Reference UID", "a"),
	0x00201000 => new CNameType("Series in Study", "A"),
	0x00201001 => new CNameType("Acquisition in Series", "A"),
	0x00201002 => new CNameType("Images in Acquisition", "A"),
	0x00201004 => new CNameType("Acquisition in Study", "A"),
	0x00201020 => new CNameType("Reference", "A"),
	0x00201040 => new CNameType("Position Reference Indicator", "A"),
	0x00201041 => new CNameType("Slice Location", "A"),
	0x00201070 => new CNameType("Other Study Numbers", "A"),
	0x00201200 => new CNameType("Number of Patient Related Studies", "A"),
	0x00201202 => new CNameType("Number of Patient Related Series", "A"),
	0x00201204 => new CNameType("Number of Patient Related Instances", "A"),
	0x00201206 => new CNameType("Number of Study Related Series", "A"),
	0x00201208 => new CNameType("Number of Study Related Instances", "A"),
	0x00201209 => new CNameType("Number of Series Related Instances", "A"),
	0x00204000 => new CNameType("Image Comments", "A"),
	0x00205000 => new CNameType("Original Image Identification(Retired)", "A"),
	0x00205002 => new CNameType("Original Image Identification Nomenclature(Retired)", "A"),
	0x00280002 => new CNameType("Samples Per Pixel", "v"),
	0x00280004 => new CNameType("Photometric Interpretation", "A"),
	0x00280006 => new CNameType("Planar Configuration", "v"),
	0x00280008 => new CNameType("Number of Frames", "A"),
	0x00280009 => new CNameType("Frame Increment Pointer", "V"),
	0x00280010 => new CNameType("Rows", "v"),
	0x00280011 => new CNameType("Columns", "v"),
	0x00280012 => new CNameType("Planes", "v"),
	0x00280014 => new CNameType("Ultrasound Color Data Present", "v"),
	0x00280030 => new CNameType("Pixel Spacing", "A"),
	0x00280031 => new CNameType("Zoom Factor", "A"),
	0x00280032 => new CNameType("Zoom Center", "A"),
	0x00280034 => new CNameType("Pixel Aspect Ratio", "A"),
	0x00280100 => new CNameType("Bits Allocated", "v"),
	0x00280101 => new CNameType("Bits Stored", "v"),
	0x00280102 => new CNameType("High Bit", "v"),
	0x00280103 => new CNameType("Pixel Representation", "v"),
	0x00280106 => new CNameType("Smallest Image Pixel Value", "v"),
	0x00280107 => new CNameType("Largest Image Pixel Value", "v"),
	0x00280200 => new CNameType("Image Location (Retired)", "A"),
	0x00281050 => new CNameType("Window Center", "A"),
	0x00281051 => new CNameType("Window Width", "A"),
	0x00281052 => new CNameType("Rescale Intercept", "A"),
	0x00281053 => new CNameType("Rescale Slope", "A"),
	0x00281054 => new CNameType("Rescale Type", "A"),
	0x00281055 => new CNameType("Window Center & Width Explanation", "A"),
	0x0032000A => new CNameType("Study Status ID", "A"),
	0x0032000C => new CNameType("Study Priority ID", "A"),
	0x00320012 => new CNameType("Study ID Issuer", "A"),
	0x00320032 => new CNameType("Study Verified Date", "A"),
	0x00320033 => new CNameType("Study Verified Time", "A"),
	0x00320034 => new CNameType("Study Read Date", "A"),
	0x00320035 => new CNameType("Study Read Time", "A"),
	0x00321000 => new CNameType("Scheduled Study Start Date", "A"),
	0x00321001 => new CNameType("Scheduled Study Start Time", "A"),
	0x00321010 => new CNameType("Scheduled Study Stop Date", "A"),
	0x00321011 => new CNameType("Scheduled Study Stop Time", "A"),
	0x00321020 => new CNameType("Scheduled Study Location", "A"),
	0x00321021 => new CNameType("Scheduled Study Location AE Titles(s)", "A"),
	0x00321030 => new CNameType("Reason for Study", "A"),
	0x00321032 => new CNameType("Requesting Physician", "A"),
	0x00321033 => new CNameType("Requesting Service", "A"),
	0x00321040 => new CNameType("Study Arrival Date", "A"),
	0x00321041 => new CNameType("Study Arrival Time", "A"),
	0x00321050 => new CNameType("Study Completion Date", "A"),
	0x00321051 => new CNameType("Study Completion Time", "A"),
	0x00321060 => new CNameType("Requested Procedure Description", "A"),
	0x00321064 => new CNameType("Requested Procedure Code Sequence", "S"),
	0x00321070 => new CNameType("Requested Contrast Agent", "A"),
	0x00324000 => new CNameType("Study Comments", "A"),
    0x00380400 => new CNameType("Patient's Institution Residence", "A"),
	0x00400100 => new CNameType("Scheduled Procedure Step Sequence", "S"),
	0x00400001 => new CNameType("Scheduled Station AE Title", "A"),
	0x00400002 => new CNameType("Scheduled Procedure Step Start Date", "A"),
	0x00400003 => new CNameType("Scheduled Procedure Step Start Time", "A"),
	0x00400006 => new CNameType("Scheduled Performing Physician's Name", "A"),
	0x00400007 => new CNameType("Scheduled Procedure Step Description", "A"),
	0x00400008 => new CNameType("Scheduled Protocol Code Sequence", "S"),
	0x00400009 => new CNameType("Scheduled Procedure Step ID", "A"),
	0x00400010 => new CNameType("Scheduled Station Name", "A"),
	0x00400011 => new CNameType("Scheduled Procedure Step Location", "A"),
	0x00400012 => new CNameType("Pre-Medication", "A"),
	0x00400020 => new CNameType("Scheduled Procedure Step Status", "A"),
	0x004008EA => new CNameType("Measurement Units Code Sequence", "S"),
	0x00401001 => new CNameType("Requested Procedure ID", "A"),
	0x00401003 => new CNameType("Requested Procedure Priority", "A"),
	0x00401004 => new CNameType("Patient Transport Arrangements", "A"),
	0x0040A010 => new CNameType("Relationship Type", "A"),
	0x0040A027 => new CNameType("Verifying Organization", "A"),
	0x0040A030 => new CNameType("Verification DateTime", "A"),
	0x0040A032 => new CNameType("Observation DateTime", "A"),
	0x0040A040 => new CNameType("Value Type", "A"),
	0x0040A043 => new CNameType("Concept Name Code Sequence", "S"),
	0x0040A050 => new CNameType("Continuity of Content", "A"),
	0x0040A073 => new CNameType("Verifying Observer Sequence", "S"),
	0x0040A075 => new CNameType("Verifying Observer Name", "A"),
	0x0040A088 => new CNameType("Verifying Observer Identification Code Sequence", "S"),
	0x0040A0B0 => new CNameType("Referenced Waveform Channels", "n"),
	0x0040A160 => new CNameType("Text Value", "A"),
	0x0040A168 => new CNameType("Concept Code Sequence", "S"),
	0x0040A300 => new CNameType("Measured Value Sequence", "S"),
	0x0040A360 => new CNameType("Predecessor Documents Sequence", "S"),
	0x0040A370 => new CNameType("Referenced Request Sequence", "S"),
	0x0040A375 => new CNameType("Current Requested Procedure Evidence Sequence", "S"),
	0x0040A385 => new CNameType("Patient Other Evidence Sequence", "S"),
	0x0040A504 => new CNameType("Content Template Sequence", "S"),
	0x0040A525 => new CNameType("Identical Documents Sequence", "S"),
	0x0040A730 => new CNameType("Content Sequence", "S"),
	0x00540010 => new CNameType("Energy Window Vector", "v"),
	0x00540011 => new CNameType("Number of Energy Windows", "v"),
	0x00540014 => new CNameType("Energy Window Lower Limit", "A"),
	0x00540015 => new CNameType("Energy Window Upper Limit", "A"),
	0x00540017 => new CNameType("Residual Syringe Counts", "A"),
	0x00540018 => new CNameType("Energy Window Name", "A"),
	0x00540020 => new CNameType("Detector Vector", "v"),
	0x00540021 => new CNameType("Number of Detectors", "v"),
	0x00540030 => new CNameType("Phase Vector", "v"),
	0x00540031 => new CNameType("Number of Phases", "v"),
	0x00540033 => new CNameType("Number of Frames in Phase", "v"),
	0x00540036 => new CNameType("Phase Delay", "A"),
	0x00540038 => new CNameType("Pause Between Frames", "A"),
	0x00540050 => new CNameType("Rotation Vector", "v"),
	0x00540051 => new CNameType("Number of Rotations", "v"),
	0x00540053 => new CNameType("Number of Frames in Rotation", "v"),
	0x00540060 => new CNameType("R-R Interval Vector", "v"),
	0x00540061 => new CNameType("Number of R-R Intervals", "v"),
	0x00540070 => new CNameType("Time Slot Vector", "v"),
	0x00540071 => new CNameType("Number of Time Slots", "v"),
	0x00540080 => new CNameType("Slice Vector", "v"),
	0x00540081 => new CNameType("Number of Slices", "v"),
	0x00540090 => new CNameType("Angular View Vector", "v"),
	0x00540100 => new CNameType("Time Slice Vector", "v"),
	0x00540101 => new CNameType("Number of Time Slices", "v"),
	0x00540200 => new CNameType("Start Angle", "A"),
	0x00540202 => new CNameType("Type of Detector Motion", "A"),
	0x00540210 => new CNameType("Trigger Vector", "A"),
	0x00540211 => new CNameType("Number of Triggers in Phase", "v"),
	0x00540400 => new CNameType("Image ID", "A"),
	0x00541000 => new CNameType("Series Type", "A"),
	0x00541001 => new CNameType("Units", "A"),
	0x00541002 => new CNameType("Counts Source", "A"),
	0x00541004 => new CNameType("Reprojection Method", "A"),
	0x00541100 => new CNameType("Randoms Correction Method", "A"),
	0x00541101 => new CNameType("Attenuation Correction Method", "A"),
	0x00541102 => new CNameType("Decay Correction", "A"),
	0x00541103 => new CNameType("Reconstruction Method", "A"),
	0x00541104 => new CNameType("Detector Lines of Response Used", "A"),
	0x00541105 => new CNameType("Scatter Correction Method", "A"),
	0x21100010 => new CNameType("Printer Status", "A"),
	0x21100020 => new CNameType("Printer Status Info", "A"),
	0x21100030 => new CNameType("Printer Name", "A"),
);
$GLOBALS['ATTR_TBL'] = $ATTR_TBL;

// explicit VR exception table
$EXPLICIT_VR_TBL = array (
    "OB"    => 2,
    "OW"    => 2,
    "OF"    => 2,
    "SQ"    => 2,
    "UT"    => 2,
    "UN"    => 2,
);

// gobal variables
$sequence = 0;

// utility functions
function pacsone_dump(&$data) {
	$length = strlen($data);
    print "<table width=100% cellpadding=0 cellspacing=0 border=1>\n";
	for ($i = 0; $i < $length; $i++) {
		if ($i % 16 == 0)
			print "<tr>";
		print "<td>" . dechex(ord($data[$i])) . "</td>";
		if ($i % 16 == 15)
			print "</tr>";
	}
	if ($i % 16 != 15)
		print "</tr>";
	print "</table>";
}

class BaseObject {

    function __construct() { }
    function __destruct() { }
	function getAttributeName($attr) {
		global $ATTR_TBL;
		return $ATTR_TBL[$attr]->name;
	}
}

class CNameType extends BaseObject {
	var $name;
	var $type;

    function __construct($name, $type) {
		$this->name = $name;
		$this->type = $type;
	}
    function __destruct() { }
}

class ApplicationContext extends BaseObject {
    var $data;
    // constant definitions
    var $CONTEXT = '1.2.840.10008.3.1.1.1';

    function __construct() {
        $this->data = pack('a' . strlen($this->CONTEXT), $this->CONTEXT);
    }
    function __destruct() { }
    function getDataBuffer() {
        $header = pack('CCn', 0x10, 0, strlen($this->data));
        return ($header . $this->data);
    }
}

class AbstractSyntax extends BaseObject {
    var $uid;
    var $data;

    function __construct($uid) {
        $this->uid = $uid;
        $this->data = pack('a' . strlen($this->uid), $this->uid);
    }
    function __destruct() { }
    function getDataBuffer() {
        $header = pack('CCn', 0x30, 0, strlen($this->data));
        return ($header . $this->data);
    }
}

class TransferSyntax extends BaseObject {
    var $data;
    // constant definitions
    var $SYNTAX = '1.2.840.10008.1.2';

    function __construct() {
        $this->data = pack('a' . strlen($this->SYNTAX), $this->SYNTAX);
    }
    function __destruct() { }
    function getDataBuffer() {
        $header = pack('CCn', 0x40, 0, strlen($this->data));
        return ($header . $this->data);
    }
}

class PresentationContext extends BaseObject {
    var $ctxId;
    var $abstract;
    var $data;

    function __construct($abs) {
        global $sequence;
        // presentation context ID
        $this->ctxId = ($sequence++ * 2 + 1) % 256;
        $this->data = pack('C4', $this->ctxId, 0, 0, 0);
        // abstract syntax
        $this->abstract = $abs;
        $absSyntax = new AbstractSyntax($this->abstract);
        $this->data .= $absSyntax->getDataBuffer();
        // transfer syntax
        $xferSyntax = new TransferSyntax();
        $this->data .= $xferSyntax->getDataBuffer();
    }
    function __destruct() { }
    function getDataBuffer() {
        $header = pack('CCn', 0x20, 0, strlen($this->data));
        return ($header . $this->data);
    }
    function getContextId() {
        return $this->ctxId;
    }
}

class MaximumPduLength extends BaseObject {
    var $maxPduLen;
    var $data;

    function __construct() {
        global $MAX_PDU_LEN;
        $this->maxPduLen = $MAX_PDU_LEN;
        $this->data = pack('N', $this->maxPduLen);
    }
    function __destruct() { }
    function getDataBuffer() {
        $header = pack('CCn', 0x51, 0, strlen($this->data));
        return ($header . $this->data);
    }
}

class ImpClassUid extends BaseObject {
    var $data;
	// constant definitions
	var $PACSONE_UID = '1.2.826.0.1.3680043.2.737';

    function __construct() {
        $this->data = pack('a' . strlen($this->PACSONE_UID), $this->PACSONE_UID);
    }
    function __destruct() { }
    function getDataBuffer() {
        $header = pack('CCn', 0x52, 0, strlen($this->data));
        return ($header . $this->data);
    }
}

class UserInformation extends BaseObject {
    var $data;

    function __construct() {
        $maxLen = new MaximumPduLength();
        $this->data = $maxLen->getDataBuffer();
		$uid = new ImpClassUid();
		$this->data .= $uid->getDataBuffer();
    }
    function __destruct() { }
    function getDataBuffer() {
        $header = pack('CCn', 0x50, 0, strlen($this->data));
        return ($header . $this->data);
    }
}

class AssociateRequestPdu extends BaseObject {
    var $calledAe;
    var $callingAe;
    var $abstract = array();
    var $accepted = array();
    var $data;

    function __construct($sopClass, $called, $calling) {
        $this->calledAe = $called;
        $this->callingAe = $calling;
        // build content of ASSOCIATE-RQ pdu
        $this->data = pack('nn', 0x1, 0);
        // add called AE title
        $this->data .= pack('A16', $this->calledAe);
        // add calling AE title
        $this->data .= pack('A16', $this->callingAe);
        // add reserved 32-bytes
        $this->data .= pack('N8', 0, 0, 0, 0, 0, 0, 0, 0);
        // add Application Context
        $applContext = new ApplicationContext();
        $this->data .= $applContext->getDataBuffer();
        // add Presentation Context
        foreach ($sopClass as $abs) {
            $presContext = new PresentationContext($abs);
            $this->abstract[$abs] = $presContext->getContextId();
            $this->data .= $presContext->getDataBuffer();
        }
        // add User Information
        $userInfo = new UserInformation();
        $this->data .= $userInfo->getDataBuffer();
    }
    function __destruct() { }
    function getDataBuffer() {
        $header = pack('CCN', 0x1, 0, strlen($this->data));
        return ($header . $this->data);
    }
    function getPresentContextId() {
        return $this->accepted[0];
    }
    function getSopClass($ctxId) {
        return array_search($ctxId, $this->abstract);
    }
    function isAccepted(&$data, &$error) {
        $result = false;
        // skip all the way to Application Context
        $skip = 68;
        $appl = substr($data, $skip);
		#pacsone_dump($appl);
        // skip the Application Context
        $header = substr($appl, 0, 4);
		$length = 4;
        $array = unpack('Ctype/Cdummy/nlength', $header);
        $length += $array['length'];
        $present = substr($appl, $length);
		#pacsone_dump($present);
        // check the Presentation Context item
        do {
            $itemType = ord($present[0]);
            $ctxId = ord($present[4]);
            $reason = ord($present[6]);
            if ( ($itemType == 0x21) && in_array($ctxId, $this->abstract) &&
                 ($reason == 0) ) {
                // use the first accepted presentation context
                $this->accepted[] = $ctxId;
                $result = true;
                break;
            }
            $header = substr($present, 0, 4);
            $array = unpack('Ctype/Cdummy/nlength', $header);
            $length = $array['length'];
            $present = substr($present, 4+$length);
        } while ($itemType == 0x21);
        if (!$result) {
            $error = 'Association request rejected, reason = ' . $reason;
        }
        return $result;
    }
}

class CEchoPdv extends BaseObject {
    var $msgId;
    var $data;

    function __construct() {
        global $C_ECHO;
        global $sequence;
        // write Affected SOP Class UID
        $length = strlen($C_ECHO);
        if ($length & 0x1)
            $length++;
        $this->data = pack('v2Va' . $length, 0, 2, $length, $C_ECHO);
        // write Command Field
        $this->data .= pack('v2Vv', 0, 0x100, 2, 0x30);
        // write Message ID
        $id = $sequence++ & 0xffffffff;
        $this->msgId = $id;
        $this->data .= pack('v2Vv', 0, 0x110, 2, $id);
        // write Data Set Type
        $this->data .= pack('v2Vv', 0, 0x800, 2, 0x101);
        #pacsone_dump($this->data);
    }
    function __destruct() { }
    function getDataBuffer() {
        // write Group Length
        $header = pack('v2V2', 0, 0, 4, strlen($this->data));
        return ($header . $this->data);
    }
    function recvResponse(&$data, &$complete, &$error) {
        $result = false;
        // skip to Group Length
        $data = substr($data, 6);
        // skip Group Length
        $data = substr($data, 12);
        // some AE (GE AW) likes to include retire data element (0000,0001) here
        $header = substr($data, 0, 8);
        $array = unpack('vgroup/velement/Vlength', $header);
        $group = $array['group'];
        $element = $array['element'];
        if (($group == 0) && ($element == 1)) {
            $data = substr($data, 12);
            $header = substr($data, 0, 8);
            $array = unpack('vgroup/velement/Vlength', $header);
        }
        // skip Affected SOP Class UID
        $length = $array['length'];
        $data = substr($data, strlen($header) + $length);
        // check Command Field
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vcommand', $header);
        $command = $array['command'];
        if ($command != 0x8030) {
            $error = 'Invalid response received: command = ' . $command;
            return false;
        }
        $data = substr($data, strlen($header));
        // check Message ID
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vid', $header);
        $id = $array['id'];
        if ($id != $this->msgId) {
            $error = 'Message ID mismatch: received = ' . $id . ', expected = ' . $this->msgId;
            return false;
        }
        $data = substr($data, strlen($header));
        // skip Data Set type
        $data = substr($data, 10);
        // check Status
        $array = unpack('vgroup/velement/Vsize/vstatus', $data);
        $status = $array['status'];
        if ($status != 0) {
            $error = 'Command failed, response status = ' . $status;
        }
        else {
            $complete = true;
            $result = true;
        }
        return $result;
    }
}

class CMatchResult extends BaseObject {
	var $attrs = array();

    function __construct($data) {
        global $ATTR_TBL;
        $total = strlen($data);
        while ($total > 0) {
            $header = substr($data, 0, 8);
            $array = unpack('vgroup/velement/Vlength', $header);
            $group = $array['group'];
            $element = $array['element'];
            $key = $group << 16 | $element;
            $length = $array['length'];
            if (isset($ATTR_TBL[$key])) {
                $body = substr($data, strlen($header), $length);
                $format = $ATTR_TBL[$key]->type;
                if (strcasecmp($format[0], "A") == 0)
                    $format .= $length;
                $format .= 'value';
                $array = unpack($format, $body);
                $value = isset($array['value'])? $array['value'] : "";
                // save this attribute
                $this->attrs[$key] = $value;
            }
            // next attribute
            $bytes = strlen($header) + $length;
            $data = substr($data, $bytes);
            $total -= $bytes;
        }
    }
    function __destruct() { }
	function hasKey($key) {
		return isset($this->attrs[$key]);
	}
	function getQueryLevel() {
		return $this->attrs[0x00080052];
	}
	function getPatientId() {
		return $this->attrs[0x00100020];
	}
	function getStudyUid() {
		return $this->attrs[0x0020000d];
	}
	function getSeriesUid() {
		return $this->attrs[0x0020000e];
	}
	function getSopInstanceUid() {
		return $this->attrs[0x00080018];
	}
}

class CFailedSopInstances extends BaseObject {
	var $list = array();

    function __construct($data) {
        $header = substr($data, 0, 8);
        $array = unpack('vgroup/velement/Vlength', $header);
        $length = $array['length'];
		$body = substr($data, strlen($header), $length);
		$array = unpack('a' . $length . 'value', $body);
		$value = isset($array['value'])? $array['value'] : "";
		// split the '\' separated UIDs
		$this->list = explode("\\", $value);
	}
    function __destruct() { }
}

class DicomObject extends BaseObject {
    var $bigEnd = false;
    // Little-Endian to Big-Endian conversion
    var $convertEndTbl = array(
        "v" => "n",
        "V" => "N",
    );
    function __construct() { }
    function __destruct() { }
    function convertEndian($format) {
        if ($this->bigEnd && isset($this->convertEndTbl[$format]))
            $format = $this->convertEndTbl[$format] ;
        return $format;
    }
}

class Sequence extends DicomObject {
	var $items = array();
	var $tag;
	var $length;

    function __construct($key, $total, &$data, $explicit, $bigEndian) {
        global $UNDEF_LEN;
        $count = 0;
		$this->tag = $key;
        $this->bigEnd = $bigEndian;
		$endSequence = 0;
        while (!$endSequence && ($total > 0)) {
            $header = substr($data, 0, 8);
            $format = $this->bigEnd? 'ngroup/nelement/Nlength' : 'vgroup/velement/Vlength';
            $array = unpack($format, $header);
            $group = $array['group'];
            $element = $array['element'];
            $length = $array['length'];
            //print "Sequence itme: Group = " . dechex($group) . " Element = " . dechex($element) . " Length = $length Format = $format<br>";
            $body = substr($data, strlen($header));
			if (($group == 0xFFFE) && ($element == 0xE000)) {			// item
				if ($length == $UNDEF_LEN)
					$length = strlen($body);
                $item = new Item($body, $length, $explicit, $bigEndian);
				$length = $item->getLength();
				$this->items[] = $item;
			} else if (($group == 0xFFFE) && ($element == 0xE0DD)) {	// sequence delimeter
				$endSequence = 1;
				// length should be 4 and the value should be 0
				if ($length != 0)
					die ("Protocol error: sequence delimeter length is not zero!");
			}
            // next item
            $bytes = strlen($header) + $length;
            $data = substr($data, $bytes);
            $total -= $bytes;
			$count += $bytes;
        }
		$this->length = $count;
	}
    function __destruct() { }
    function getTag() {
		return $this->tag;
	}
    function getLength() {
		return $this->length;
	}
	function hasKey($key) {
        foreach ($this->items as $item) {
            if ($item->hasKey($key))
                return true;
        }
        return false;
    }
    function getAttr($key) {
        $value = "";
        foreach ($this->items as $item) {
            if ($item->hasKey($key)) {
                $value = $item->getAttr($key);
                break;
            }
        }
        return $value;
    }
    function getItem($key) {
        $value = "";
        foreach ($this->items as $item) {
            if ($item->hasKey($key)) {
                $value = $item->getItem($key);
                break;
            }
        }
        return $value;
    }
	function showDebug() {
		print "<b>Sequence Attribute (" . dechex($this->tag) . "):<br></b>";
        foreach ($this->items as $key => $item) {
			print "Sequence Item (" . dechex($key) . "):<br>";
			$item->showDebug();
			print "End of Sequence Item (" . dechex($key) . ").<br>";
		}
		print "<b>End of Sequence Attribute (" . dechex($this->tag) . ").<br></b>";
	}
    function isEmpty() {
        return count($this->items)? false : true;
    }
}

class Item extends DicomObject {
	var $attrs = array();
	var $length;

    function __construct(&$data, $total, $explicit, $bigEndian) {
		global $ATTR_TBL;
		global $EXPLICIT_VR_TBL;
        global $UNDEF_LEN;
        $this->bigEnd = $bigEndian;
		$count = 0;
		$endItem = 0;
        while (!$endItem && ($total > 0)) {
            $header = substr($data, 0, 4);
            $format = $this->bigEnd? 'ngroup/nelement' : 'vgroup/velement';
            $array = unpack($format, $header);
            $group = $array['group'];
            $element = $array['element'];
			$key = ($group << 16) + $element;
			if (($group == 0xFFFE) && ($element == 0xE00D)) {	// item delimeter
                $value = substr($data, strlen($header), 4);
                $format = $this->bigEnd? 'Nlength' : 'Vlength';
                $array = unpack($format, $value);
                $length = $array['length'];
                $header .= $value;
				$endItem = 1;
				// length should be 4 and the value should be 0
				if ($length != 0)
					die ("Protocol error: item delimeter length is not zero!");
			} else {
                if ($explicit) {
                    $value = substr($data, strlen($header), 2);
        	        $array = unpack('A2vr', $value);
			        $vr = $array['vr'];
                    $header .= $value;
                    if (isset($EXPLICIT_VR_TBL[$vr])) {
                        $format = $this->bigEnd? "nreserved/Nlength" : "vreserved/Vlength";
                        $offset = 6;
                    } else {
                        $format = $this->bigEnd? "nlength" : "vlength";
                        $offset = 2;
                    }
                    $value = substr($data, strlen($header), $offset);
                    $array = unpack($format, $value);
                    $length = $array['length'];
                    $header .= $value;
                } else {
                    $value = substr($data, strlen($header), 4);
                    $format = $this->bigEnd? 'Nlength' : 'Vlength';
                    $array = unpack($format, $value);
                    $length = $array['length'];
                    $header .= $value;
                }
				if (isset($ATTR_TBL[$key]))
					$format = $this->convertEndian($ATTR_TBL[$key]->type);
				else
					$format = ($element == 0)? $this->convertEndian("V") : "A";
				//print "Key = " . dechex($key) . " Length = $length Format = $format<br>";
				$body = substr($data, strlen($header));
				if ( ($length == $UNDEF_LEN) ||
                     (strcasecmp($format[0], "S") == 0) ) {		// sequence
					if ($length == $UNDEF_LEN)
						$length = strlen($body);
					$this->attrs[$key] = new Sequence($key, $length, $body, $explicit, $bigEndian);
					$length = $this->attrs[$key]->getLength();
				} else {
					if (strcasecmp($format[0], "A") == 0)
						$format .= $length;
					$format .= 'value';
					$array = unpack($format, $body);
			        $value = isset($array['value'])? $array['value'] : "";
					// save this attribute
					$this->attrs[$key] = $value;
				}
			}
            // next attribute
            $bytes = strlen($header) + $length;
            $data = substr($data, $bytes);
            $total -= $bytes;
            $count += $bytes;
        }
		$this->length = $count;
	}
    function __destruct() { }
	function hasKey($key) {
		if (isset($this->attrs[$key]))
            return true;
        foreach ($this->attrs as $attr) {
            if (is_a($attr, 'Sequence') && $attr->hasKey($key))
                return true;
        }
        return false;
	}
    function getLength() {
		return $this->length;
	}
    function getAttr($key) {
        $obj = $this->attrs[$key];
        if (is_a($obj, 'Sequence')) {
            $value = $obj->getAttr($key);
        } else {
            $value = $this->attrs[$key];
        }
		return $value;
	}
    function getItem($key) {
		if (isset($this->attrs[$key]))
		    return $this->attrs[$key];
        foreach ($this->attrs as $attr) {
            if (is_a($attr, 'Sequence') && $attr->hasKey($key))
                return $attr->getItem($key);
        }
        return false;
	}
	function showDebug() {
        foreach ($this->attrs as $key => $attr) {
            if (is_a($attr, 'Sequence'))
				$attr->showDebug();
			else
				print "Attribute (" . dechex($key) . "): Value = $attr<br>";
		}
	}
}

class CAttributeList extends BaseObject {
	var $attrs = array();

    function __construct($data) {
		global $ATTR_TBL;
        $total = strlen($data);
        while ($total > 0) {
            $header = substr($data, 0, 8);
            $array = unpack('vgroup/velement/Vlength', $header);
            $group = $array['group'];
            $element = $array['element'];
			$key = $group << 16 | $element;
            $length = $array['length'];
			$body = substr($data, strlen($header), $length);
			$format = $ATTR_TBL[$key]->type;
			if (strcasecmp($format[0], "A") == 0)
				$format .= $length;
			$format .= 'value';
			$array = unpack($format, $body);
			$value = isset($array['value'])? $array['value'] : "";
			// save this attribute
			$this->attrs[$key] = $value;
            // next attribute
            $bytes = strlen($header) + $length;
            $data = substr($data, $bytes);
            $total -= $bytes;
        }
	}
    function __destruct() { }
}

class CFindPdv extends BaseObject {
    var $finalStatus;
    var $msgId;
    var $data;
	// C-FIND response status table
	var $STATUS_TBL = array (
		0xA700 => "Refused: Out of Resources",
		0xA900 => "Failed: Identifier does not match SOP class",
		0xFE00 => "Cancel: Matching terminated due to Cancel request",
	);

    function __construct($sopClass) {
        global $sequence;
        // write Affected SOP Class UID
        $length = strlen($sopClass);
        if ($length & 0x1)
            $length++;
        $this->data = pack('v2Va' . $length, 0, 2, $length, $sopClass);
        // write Command Field
        $this->data .= pack('v2Vv', 0, 0x100, 2, 0x20);
        // write Message ID
        $id = $sequence++ & 0xffffffff;
        $this->msgId = $id;
        $this->data .= pack('v2Vv', 0, 0x110, 2, $id);
        // write Priority
        $this->data .= pack('v2Vv', 0, 0x700, 2, 0);
        // write Data Set Type
        $this->data .= pack('v2Vv', 0, 0x800, 2, 0);
        #pacsone_dump($this->data);
		$this->finalStatus = 0xFF00;
    }
    function __destruct() { }
    function getDataBuffer() {
        // write Group Length
        $header = pack('v2V2', 0, 0, 4, strlen($this->data));
        return ($header . $this->data);
    }
    function recvResponse(&$data, &$complete, &$error) {
		// check PDV header to see if it's a Command or Dataset
		$msgHdr = ord($data[5]);
        // skip to Group Length
        $data = substr($data, 6);
		if ($msgHdr & 0x1) {
			$result = $this->recvCmdResponse($data, $error);
		} else {
			$result = $this->recvDataset($data, $error);
		}
		if (($this->finalStatus & 0xFFFE) != 0xFF00) {
			if ($this->finalStatus)
				$error = "C-FIND Response: " . $this->STATUS_TBL[ $this->finalStatus ];
			$complete = true;
		}
		return $result;
	}
    function recvCmdResponse(&$data, &$error) {
        $result = true;
        // skip Group Length
        $data = substr($data, 12);
        // some AE (GE AW) likes to include retire data element (0000,0001) here
        $header = substr($data, 0, 8);
        $array = unpack('vgroup/velement/Vlength', $header);
        $group = $array['group'];
        $element = $array['element'];
        if (($group == 0) && ($element == 1)) {
            $data = substr($data, 12);
            $header = substr($data, 0, 8);
            $array = unpack('vgroup/velement/Vlength', $header);
        }
        // skip Affected SOP Class UID
        $length = $array['length'];
        $data = substr($data, strlen($header) + $length);
        // check Command Field
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vcommand', $header);
        $command = $array['command'];
        if ($command != 0x8020) {
            $error = 'Invalid response received: command = ' . $command;
            return false;
        }
        $data = substr($data, strlen($header));
        // check Message ID Being Responded to
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vid', $header);
        // some applications (eFilm) inserts the non-conforming Message ID here
        $group = $array['group'];
        $element = $array['element'];
        if (($group == 0) && ($element == 0x110)) {
            $data = substr($data, strlen($header));
            $header = substr($data, 0, 10);
            $array = unpack('vgroup/velement/Vsize/vid', $header);
        }
        $id = $array['id'];
        if ($id != $this->msgId) {
            $error = 'Message ID mismatch: received = ' . $id . ', expected = ' . $this->msgId;
            return false;
        }
        $data = substr($data, strlen($header));
        // check Data Set type
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vtype', $header);
        $group = $array['group'];
        $element = $array['element'];
        // some applications (eFilm) inserts the non-conforming Priority here
        if (($group == 0) && ($element == 0x700)) {
            $data = substr($data, strlen($header));
            $header = substr($data, 0, 10);
            $array = unpack('vgroup/velement/Vsize/vtype', $header);
            $group = $array['group'];
            $element = $array['element'];
        }
		if ($group != 0 or $element != 0x800) {
            $error = "Invalid Dataset Type: group = " . dechex($group);
			$error .= " element = " . dechex($element);
            return false;
		}
        $type = $array['type'];
        $data = substr($data, strlen($header));
        // check Status
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vstatus', $header);
        $status = $array['status'];
        if (($status & 0xFFFE) != 0xFF00) {
			$this->finalStatus = $status;
            $result = false;
        }
        // parse returned Identifier
        if (($type != 0x101) && (strlen($data) > strlen($header))) {   // dataset present
            $data = substr($data, strlen($header));
        	// skip the Data Set PDV header
        	$data = substr($data, 6);
			$result = $this->recvDataset($data, $error);
        }
        return $result;
    }
    function recvDataset(&$data, &$error) {
        if (!strlen($data))
            return false;
		$result = new CMatchResult($data);
		return $result;
	}
}

class WorklistFindPdv extends BaseObject {
    var $finalStatus;
    var $msgId;
    var $data;
	// Modality Worklist-FIND response status table
	var $STATUS_TBL = array (
		0xA700 => "Refused: Out of Resources",
		0xA900 => "Failed: Identifier does not match SOP class",
		0xFE00 => "Cancel: Matching terminated due to Cancel request",
	);

    function __construct() {
        global $WORKLIST_FIND;
        global $sequence;
        // write Affected SOP Class UID
        $length = strlen($WORKLIST_FIND);
        if ($length & 0x1)
            $length++;
        $this->data = pack('v2Va' . $length, 0, 2, $length, $WORKLIST_FIND);
        // write Command Field
        $this->data .= pack('v2Vv', 0, 0x100, 2, 0x20);
        // write Message ID
        $id = $sequence++ & 0xffffffff;
        $this->msgId = $id;
        $this->data .= pack('v2Vv', 0, 0x110, 2, $id);
        // write Priority
        $this->data .= pack('v2Vv', 0, 0x700, 2, 0);
        // write Data Set Type
        $this->data .= pack('v2Vv', 0, 0x800, 2, 0);
        #pacsone_dump($this->data);
		$this->finalStatus = 0xFF00;
    }
    function __destruct() { }
    function getDataBuffer() {
        // write Group Length
        $header = pack('v2V2', 0, 0, 4, strlen($this->data));
        return ($header . $this->data);
    }
    function recvResponse(&$data, &$complete, &$error) {
		// check PDV header to see if it's a Command or Dataset
		$msgHdr = ord($data[5]);
        // skip to Group Length
        $data = substr($data, 6);
		if ($msgHdr & 0x1) {
			$result = $this->recvCmdResponse($data, $error);
		} else {
			$result = $this->recvDataset($data, $error);
		}
		if (($this->finalStatus & 0xFFFE) != 0xFF00) {
			if ($this->finalStatus)
				$error = "ModalityWorklist-FIND Response: " . $this->STATUS_TBL[ $this->finalStatus ];
			$complete = true;
		}
		return $result;
	}
    function recvCmdResponse(&$data, &$error) {
        $result = false;
        // skip Group Length
        $data = substr($data, 12);
        // some AE (GE AW) likes to include retire data element (0000,0001) here
        $header = substr($data, 0, 8);
        $array = unpack('vgroup/velement/Vlength', $header);
        $group = $array['group'];
        $element = $array['element'];
        if (($group == 0) && ($element == 1)) {
            $data = substr($data, 12);
            $header = substr($data, 0, 8);
            $array = unpack('vgroup/velement/Vlength', $header);
        }
        // skip Affected SOP Class UID
        $length = $array['length'];
        $data = substr($data, strlen($header) + $length);
        // check Command Field
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vcommand', $header);
        $command = $array['command'];
        if ($command != 0x8020) {
            $error = 'Invalid response received: command = ' . $command;
            return false;
        }
        $data = substr($data, strlen($header));
        // check Message ID
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vid', $header);
        $id = $array['id'];
        if ($id != $this->msgId) {
            $error = 'Message ID mismatch: received = ' . $id . ', expected = ' . $this->msgId;
            return false;
        }
        $data = substr($data, strlen($header));
        // check Data Set type
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vtype', $header);
        $group = $array['group'];
        $element = $array['element'];
		if ($group != 0 or $element != 0x800) {
            $error = "Invalid Dataset Type: group = " . dechex($group);
			$error .= " element = " . dechex($element);
            return false;
		}
        $type = $array['type'];
        $data = substr($data, strlen($header));
        // check Status
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vstatus', $header);
        $status = $array['status'];
        if (($status & 0xFFFE) != 0xFF00) {
			$this->finalStatus = $status;
            $result = false;
        }
        $data = substr($data, strlen($header));
        // parse returned Identifier
        if (($type != 0x101) && strlen($data)) {   // dataset present
        	// skip the Data Set PDV header
        	$data = substr($data, 6);
			$result = $this->recvDataset($data, $error);
        }
        return $result;
    }
    function recvDataset(&$data, &$error) {
		$result = new Item($data, strlen($data), false, false);
		return $result;
	}
}

class GetPrinterPdv extends BaseObject {
    var $msgId;
    var $data;

    function __construct() {
        global $sequence;
        // write Affected SOP Class UID
        $uid = "1.2.840.10008.5.1.1.16";
        $length = strlen($uid);
        if ($length & 0x1)
            $length++;
        $this->data = pack('v2Va' . $length, 0, 3, $length, $uid);
        // write Command Field
        $this->data .= pack('v2Vv', 0, 0x100, 2, 0x0110);
        // write Message ID
        $id = $sequence++ & 0xffffffff;
        $this->msgId = $id;
        $this->data .= pack('v2Vv', 0, 0x110, 2, $id);
        // write Data Set Type
        $this->data .= pack('v2Vv', 0, 0x800, 2, 0x0101);
        // write Requested SOP Instance UID
        $uid = "1.2.840.10008.5.1.1.17";
        $length = strlen($uid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2Va' . $length, 0, 0x1001, $length, $uid);
        #pacsone_dump($this->data);
    }
    function __destruct() { }
    function getDataBuffer() {
        // write Group Length
        $header = pack('v2V2', 0, 0, 4, strlen($this->data));
        return ($header . $this->data);
    }
    function recvResponse(&$data, &$complete, &$error) {
		// check PDV header to see if it's a Command or Dataset
		$msgHdr = ord($data[5]);
        // skip to Group Length
        $data = substr($data, 6);
		if ($msgHdr & 0x1) {
			$result = $this->recvCmdResponse($data, $error, $complete);
		} else {
			$result = $this->recvDataset($data, $error);
			$complete = true;
		}
		return $result;
	}
    function recvCmdResponse(&$data, &$error, &$complete) {
        $result = false;
        // skip Group Length
        $data = substr($data, 12);
        // some AE (GE AW) likes to include retire data element (0000,0001) here
        $header = substr($data, 0, 8);
        $array = unpack('vgroup/velement/Vlength', $header);
        $group = $array['group'];
        $element = $array['element'];
        if (($group == 0) && ($element == 1)) {
            $data = substr($data, 12);
            $header = substr($data, 0, 8);
            $array = unpack('vgroup/velement/Vlength', $header);
        }
        // skip Affected SOP Class UID
        $length = $array['length'];
        $data = substr($data, strlen($header) + $length);
        // check Command Field
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vcommand', $header);
        $command = $array['command'];
        if ($command != 0x8110) {
            $error = 'Invalid response received: command = ' . $command;
            return false;
        }
        $data = substr($data, strlen($header));
        // check Message ID
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vid', $header);
        $id = $array['id'];
        if ($id != $this->msgId) {
            $error = 'Message ID mismatch: received = ' . $id . ', expected = ' . $this->msgId;
            return false;
        }
        $data = substr($data, strlen($header));
        // check Data Set type
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vtype', $header);
        $group = $array['group'];
        $element = $array['element'];
		if ($group != 0 or $element != 0x800) {
            $error = "Invalid Dataset Type: group = " . dechex($group);
			$error .= " element = " . dechex($element);
            return false;
		}
        $type = $array['type'];
        if ($type == 0x101) {
            $complete = true;
        }
        $data = substr($data, strlen($header));
        // check Status
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vstatus', $header);
        $status = $array['status'];
        if ($status != 0) {
			$error = "N-Get Response: $status";
            $result = false;
        }
        $data = substr($data, strlen($header));
        // skip Affected SOP Instance UID
        $header = substr($data, 0, 8);
        $array = unpack('vgroup/velement/Vlength', $header);
        $length = $array['length'];
        $data = substr($data, strlen($header) + $length);
        // parse returned Identifier
        if (($type != 0x101) && strlen($data)) {   // dataset present
        	// skip the Data Set PDV header
        	$data = substr($data, 6);
			$result = $this->recvDataset($data, $error);
        }
        return $result;
    }
    function recvDataset(&$data, &$error) {
		$result = new CAttributeList($data);
		return $result;
	}
}

class CMovePdv extends BaseObject {
    var $msgId;
    var $finalStatus;
    var $numRemaining;
    var $numCompleted;
    var $numFailed;
    var $numWarning;
    var $data;
	// C-MOVE response status table
	var $STATUS_TBL = array (
		0xA701 => "Refused: Out of Resources - Unable to calculate number of matches",
		0xA702 => "Refused: Out of Resources - Unable to perform sub-operations",
		0xA801 => "Refused: Move Destination Unknown",
		0xA900 => "Failed: Identifier does not match SOP class",
		0xFE00 => "Cancel: Sub-operations terminated due to Cancel indication",
		0xB000 => "Warning: Sub-operations Complete - One or more failures",
	);

    function __construct($sopClass, $dest) {
        global $sequence;
        // write Affected SOP Class UID
        $length = strlen($sopClass);
        if ($length & 0x1)
            $length++;
        $this->data = pack('v2Va' . $length, 0, 2, $length, $sopClass);
        // write Command Field
        $this->data .= pack('v2Vv', 0, 0x100, 2, 0x21);
        // write Message ID
        $id = $sequence++ & 0xffffffff;
        $this->msgId = $id;
        $this->data .= pack('v2Vv', 0, 0x110, 2, $id);
		// write Move Destination
		$length = strlen($dest);
		if ($length & 0x1)
			$length++;
		$this->data .= pack('v2VA' . $length, 0, 0x600, $length, $dest);
        // write Priority
        $this->data .= pack('v2Vv', 0, 0x700, 2, 0);
        // write Data Set Type
        $this->data .= pack('v2Vv', 0, 0x800, 2, 0);
        #pacsone_dump($this->data);
		$this->finalStatus = 0xFF00;
		$this->numRemaining = 0;
		$this->numCompleted = 0;
		$this->numFailed = 0;
		$this->numWarning = 0;
    }
    function __destruct() { }
    function getDataBuffer() {
        // write Group Length
        $header = pack('v2V2', 0, 0, 4, strlen($this->data));
        return ($header . $this->data);
    }
    function recvResponse(&$data, &$complete, &$error) {
		// check PDV header to see if it's a Command or Dataset
		$msgHdr = ord($data[5]);
        // skip to Group Length
        $data = substr($data, 6);
		if ($msgHdr & 0x1) {
			$result = $this->recvCmdResponse($data, $error);
		} else {
			$result = $this->recvDataset($data, $error);
		}
		if ($this->finalStatus != 0xFF00) {
			if ($this->finalStatus) {
				$error = "C-MOVE Response: ";
				if (($this->finalStatus & 0xC000) == 0xC000)
					$error .= "Failed: Unable to Process";
				else
					$error .= $this->STATUS_TBL[ $this->finalStatus ];
			}
			$complete = true;
		}
		return $result;
	}
    function recvCmdResponse(&$data, &$error) {
        $result = true;
        // skip Group Length
        $data = substr($data, 12);
        // some AE (GE AW) likes to include retire data element (0000,0001) here
        $header = substr($data, 0, 8);
        $array = unpack('vgroup/velement/Vlength', $header);
        $group = $array['group'];
        $element = $array['element'];
        if (($group == 0) && ($element == 1)) {
            $data = substr($data, 12);
            $header = substr($data, 0, 8);
            $array = unpack('vgroup/velement/Vlength', $header);
        }
        // skip Affected SOP Class UID
        $length = $array['length'];
        $data = substr($data, strlen($header) + $length);
        // check Command Field
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vcommand', $header);
        $command = $array['command'];
        if ($command != 0x8021) {
            $error = 'Invalid response received: command = ' . $command;
            return false;
        }
        $data = substr($data, strlen($header));
        // check Message ID
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vid', $header);
        $id = $array['id'];
        if ($id != $this->msgId) {
            $error = 'Message ID mismatch: received = ' . $id . ', expected = ' . $this->msgId;
            return false;
        }
        $data = substr($data, strlen($header));
        // check Data Set type
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vtype', $header);
        $type = $array['type'];
        $data = substr($data, strlen($header));
        // check Status
        $header = substr($data, 0, 10);
        $array = unpack('vgroup/velement/Vsize/vstatus', $header);
        $status = $array['status'];
        $data = substr($data, strlen($header));
		// check Number of Remaining Sub-operations
		// check Number of Completed Sub-operations
		// check Number of Failed Sub-operations
		// check Number of Warning Sub-operations
        while (strlen($data)) {
            $header = substr($data, 0, 10);
            $array = unpack('vgroup/velement/Vsize/vnumber', $header);
            $group = $array['group'];
            $element = $array['element'];
            switch ($element) {
            case 0x1020:
                $this->numRemaining = $array['number'];
                break;
            case 0x1021:
                $this->numCompleted = $array['number'];
                break;
            case 0x1022:
                $this->numFailed = $array['number'];
                break;
            case 0x1023:
                $this->numWarning = $array['number'];
                break;
            default:
                $group = 1;
                break;
            }
            if ($group != 0x0)
                break;
            $data = substr($data, strlen($header));
        }
		// check final status
        if ($status != 0xFF00) {
			$this->finalStatus = $status;
            $result = false;
        }
        // parse returned Identifier
        if ($type != 0x101) {   // dataset present
        	// skip the Data Set PDV header
        	$data = substr($data, 6);
            // skip empty dataset returned by Siemens MagicView
            if (strlen($data))
			    $result = $this->recvDataset($data, $error);
            else
                $result = false;
        }
        return $result;
    }
    function recvDataset(&$data, &$error) {
		// the Dataset should contain a list of failed SOP instances
		$result = new CFailedSopInstances($data);
		return $result;
	}
}

class CFindIdentifierRoot extends BaseObject {
    var $attrs = array();
    var $data;
    var $id;
    var $last;
    var $first;
    var $optionalKeys;

    function __construct($id, $last, $first) {
        $this->id = $id;
        $this->last = $last;
        $this->first = $first;
        $this->optionalKeys = true;
        // add Query/Retrieve Level
        $level = 'PATIENT';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data = pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
        // add Patient Name
		$fullname = "";
		if (strlen($last) || strlen($first))
			$fullname = $last . "^" . $first;
		$length = strlen($fullname);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x10, 0x10, $length);
		if ($length)
			$this->data .= pack('A' . $length, $fullname);
		$this->attrs[] = 0x00100010;
        // add Patient ID
		$length = strlen($id);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x10, 0x20, $length);
		if ($length)
			$this->data .= pack('A' . $length, $id);
		$this->attrs[] = 0x00100020;
        // add Patient Birth Date
        $this->data .= pack('v2V', 0x10, 0x30, 0);
		$this->attrs[] = 0x00100030;
        // add Patient Sex
        $this->data .= pack('v2V', 0x10, 0x40, 0);
		$this->attrs[] = 0x00100040;
        // add optional keys
        if ($this->optionalKeys) {
            // add Number of Patient Related Studies
            $this->data .= pack('v2V', 0x20, 0x1200, 0);
		    $this->attrs[] = 0x00201200;
            // add Number of Patient Related Series
            $this->data .= pack('v2V', 0x20, 0x1202, 0);
		    $this->attrs[] = 0x00201202;
            // add Number of Patient Related Instances
            $this->data .= pack('v2V', 0x20, 0x1204, 0);
		    $this->attrs[] = 0x00201204;
        }
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
    function studyRoot() {  // change into Study-Root Informational Model
        // add Study Date
        $this->data = pack('v2V', 0x8, 0x20, 0);
		$this->attrs[] = 0x00080020;
        // add Study Time
        $this->data = pack('v2V', 0x8, 0x30, 0);
		$this->attrs[] = 0x00080030;
        // add Accession Number
        $this->data .= pack('v2V', 0x8, 0x50, 0);
		$this->attrs[] = 0x00080050;
        // add Query/Retrieve Level
        $level = 'STUDY';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data = pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
        // add Patient Name
		$fullname = "";
		if (strlen($this->last) || strlen($this->first))
			$fullname = $this->last . "^" . $this->first;
		$length = strlen($fullname);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x10, 0x10, $length);
		if ($length)
			$this->data .= pack('A' . $length, $fullname);
		$this->attrs[] = 0x00100010;
        // add Patient ID
		$length = strlen($this->id);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x10, 0x20, $length);
		if ($length)
			$this->data .= pack('A' . $length, $this->id);
		$this->attrs[] = 0x00100020;
        // add Patient Birth Date
        $this->data .= pack('v2V', 0x10, 0x30, 0);
		$this->attrs[] = 0x00100030;
        // add Patient Sex
        $this->data .= pack('v2V', 0x10, 0x40, 0);
		$this->attrs[] = 0x00100040;
        // add Study Instance UID
        $this->data .= pack('v2V', 0x20, 0xd, 0);
		$this->attrs[] = 0x0020000d;
        // add Study ID
        $this->data .= pack('v2V', 0x20, 0x10, 0);
		$this->attrs[] = 0x00200010;
        // add optional keys
        if ($this->optionalKeys) {
            // add Number of Study Related Series
            $this->data .= pack('v2V', 0x20, 0x1206, 0);
		    $this->attrs[] = 0x00201206;
            // add Number of Study Related Instances
            $this->data .= pack('v2V', 0x20, 0x1208, 0);
		    $this->attrs[] = 0x00201208;
        }
    }
}

class CFindIdentifierPatient extends BaseObject {
    var $attrs = array();
    var $data;
    var $optionalKeys;

    function __construct($patientid, $studyid, $date, $accession, $referdoc) {
        $this->optionalKeys = true;
		// add Study Date
		$length = strlen($date);
		if ($length & 0x1)
			$length++;
        $this->data = pack('v2V', 0x8, 0x20, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $date);
		$this->attrs[] = 0x00080020;
		// add Accession Number
		$length = strlen($accession);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x8, 0x50, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $accession);
		$this->attrs[] = 0x00080050;
        // add Query/Retrieve Level
        $level = 'STUDY';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
		// add Referring Physician's Name
		$length = strlen($referdoc);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x8, 0x90, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $referdoc);
		$this->attrs[] = 0x00080090;
		// add Study Description
        $this->data .= pack('v2V', 0x8, 0x1030, 0);
		$this->attrs[] = 0x00081030;
        // add Patient ID
		$length = strlen($patientid);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x10, 0x20, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $patientid);
		$this->attrs[] = 0x00100020;
		// add Study Instance UID
        $this->data .= pack('v2V', 0x20, 0xd, 0);
		$this->attrs[] = 0x0020000d;
		// add Study ID
		$length = strlen($studyid);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x20, 0x10, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $studyid);
		$this->attrs[] = 0x00200010;
        // add optional keys
        if ($this->optionalKeys) {
            // add Number of Study Related Series
            $this->data .= pack('v2V', 0x20, 0x1206, 0);
		    $this->attrs[] = 0x00201206;
            // add Number of Study Related Instances
            $this->data .= pack('v2V', 0x20, 0x1208, 0);
		    $this->attrs[] = 0x00201208;
        }
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
}

class CFindIdentifierStudyRoot extends BaseObject {
    var $attrs = array();
    var $data;
    var $optionalKeys;

    function __construct($studyid, $date, $accession, $referdoc) {
        $this->optionalKeys = true;
		// add Study Date
		$length = strlen($date);
		if ($length & 0x1)
			$length++;
        $this->data = pack('v2V', 0x8, 0x20, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $date);
		$this->attrs[] = 0x00080020;
		// add Study Time
		$this->data .= pack('v2V', 0x8, 0x30, 0);
		$this->attrs[] = 0x00080030;
		// add Accession Number
		$length = strlen($accession);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x8, 0x50, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $accession);
		$this->attrs[] = 0x00080050;
        // add Query/Retrieve Level
        $level = 'STUDY';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
		// add Referring Physician's Name
		$length = strlen($referdoc);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x8, 0x90, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $referdoc);
		$this->attrs[] = 0x00080090;
		// add Study Description
        $this->data .= pack('v2V', 0x8, 0x1030, 0);
		$this->attrs[] = 0x00081030;
        // add Patient Name
        $this->data .= pack('v2V', 0x10, 0x10, 0);
		$this->attrs[] = 0x00100010;
        // add Patient ID
        $this->data .= pack('v2V', 0x10, 0x20, 0);
		$this->attrs[] = 0x00100020;
		// add Study Instance UID
        $this->data .= pack('v2V', 0x20, 0xd, 0);
		$this->attrs[] = 0x0020000d;
		
		$this->data .= pack('v2V', 0x8, 0x60, 0);		
		$this->attrs[] = 0x00080060;
		
		// add Study ID
		$length = strlen($studyid);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x20, 0x10, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $studyid);
		$this->attrs[] = 0x00200010;
        // add optional keys
        if ($this->optionalKeys) {
            // add Number of Study Related Series
            $this->data .= pack('v2V', 0x20, 0x1206, 0);
		    $this->attrs[] = 0x00201206;
            // add Number of Study Related Instances
            $this->data .= pack('v2V', 0x20, 0x1208, 0);
		    $this->attrs[] = 0x00201208;
        }
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
}

class CFindIdentifierStudy extends BaseObject {
    var $attrs = array();
    var $data;
    var $optionalKeys;

    function __construct($patientid, $uid, $modality, $date) {
        $this->optionalKeys = true;
		// add Series Date
		$length = strlen($date);
		if ($length & 0x1)
			$length++;
        $this->data = pack('v2V', 0x8, 0x21, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $date);
		$this->attrs[] = 0x00080021;
        // add Query/Retrieve Level
        $level = 'SERIES';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
        // add Modality
		$length = strlen($modality);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x8, 0x60, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $modality);
		$this->attrs[] = 0x00080060;
        // add Patient ID
        $length = strlen($patientid);
        if ($length) {
            if ($length & 0x1)
                $length++;
            $this->data .= pack('v2VA' . $length, 0x10, 0x20, $length, $patientid);
		    $this->attrs[] = 0x00100020;
        }
        // add Body Part Examined
        $this->data .= pack('v2V', 0x18, 0x15, 0);
		$this->attrs[] = 0x00180015;
		// add Study Instance UID
        $length = strlen($uid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2V', 0x20, 0xd, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $uid);
		$this->attrs[] = 0x0020000d;
		// add Series Instance UID
        $this->data .= pack('v2V', 0x20, 0xe, 0);
		$this->attrs[] = 0x0020000e;
		// add Series Number
        $this->data .= pack('v2V', 0x20, 0x11, 0);
		$this->attrs[] = 0x00200011;
        if ($this->optionalKeys) {
		    // add Number of Series Related Instances
            $this->data .= pack('v2V', 0x20, 0x1209, 0);
		    $this->attrs[] = 0x00201209;
        }
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
	function getDisplayAttrs() {
		$attrs = array();
		foreach ($this->attrs as $attr) {
			// skip displaying Study UID
			if ($attr != 0x0020000d)
				$attrs[] = $attr;
		}
		return $attrs;
	}
}

class CFindIdentifierSeries extends BaseObject {
    var $attrs = array();
    var $data;

    function __construct($patientid, $studyuid, $uid) {
		// add SOP Instance UID
        $this->data = pack('v2V', 0x8, 0x18, 0);
		$this->attrs[] = 0x00080018;
        // add Query/Retrieve Level
        $level = 'IMAGE';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
        // add Patient ID
        $length = strlen($patientid);
        if ($length) {
            if ($length & 0x1)
                $length++;
            $this->data .= pack('v2VA' . $length, 0x10, 0x20, $length, $patientid);
		    $this->attrs[] = 0x00100020;
        }
		// add Study Instance UID
        $length = strlen($studyuid);
        if ($length) {
            if ($length & 0x1)
                $length++;
            $this->data .= pack('v2Va' . $length, 0x20, 0xd, $length, $studyuid);
		    $this->attrs[] = 0x0020000d;
        }
		// add Series Instance UID
        $length = strlen($uid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2Va' . $length, 0x20, 0xe, $length, $uid);
		$this->attrs[] = 0x0020000e;
		// add Instance Number
        $this->data .= pack('v2V', 0x20, 0x13, 0);
		$this->attrs[] = 0x00200013;
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
	function getDisplayAttrs() {
		$attrs = array();
		foreach ($this->attrs as $attr) {
			// skip displaying Study UID and Series UID
			if ($attr != 0x0020000d && $attr != 0x0020000e)
				$attrs[] = $attr;
		}
		return $attrs;
	}
}

class CFindIdentifierImage extends BaseObject {
    var $attrs = array();
    var $data;

    function __construct($patientid, $studyuid, $seriesuid, $uid) {
		// add SOP Instance UID
        $length = strlen($uid);
        if ($length & 0x1)
            $length++;
        $this->data = pack('v2Va' . $length, 0x8, 0x18, $length, $uid);
		$this->attrs[] = 0x00080018;
        // add Query/Retrieve Level
        $level = 'IMAGE';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
        // add Patient ID
        $length = strlen($patientid);
        if ($length) {
            if ($length & 0x1)
                $length++;
            $this->data .= pack('v2VA' . $length, 0x10, 0x20, $length, $patientid);
		    $this->attrs[] = 0x00100020;
        }
		// add Study Instance UID
        $length = strlen($studyuid);
        if ($length) {
            if ($length & 0x1)
                $length++;
            $this->data .= pack('v2Va' . $length, 0x20, 0xd, $length, $studyuid);
		    $this->attrs[] = 0x0020000d;
        }
		// add Series Instance UID
        $length = strlen($seriesuid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2Va' . $length, 0x20, 0xe, $length, $seriesuid);
        $this->attrs[] = 0x0020000e;
		// add Instance Number
        $this->data .= pack('v2V', 0x20, 0x13, 0);
		$this->attrs[] = 0x00200013;
		// add Samples Per Pixel
        $this->data .= pack('v2V', 0x28, 0x2, 0);
		$this->attrs[] = 0x00280002;
		// add Rows
        $this->data .= pack('v2V', 0x28, 0x10, 0);
		$this->attrs[] = 0x00280010;
		// add Columns
        $this->data .= pack('v2V', 0x28, 0x11, 0);
		$this->attrs[] = 0x00280011;
		// add Bits Allocated
        $this->data .= pack('v2V', 0x28, 0x100, 0);
		$this->attrs[] = 0x00280100;
		// add Bits Stored
        $this->data .= pack('v2V', 0x28, 0x101, 0);
		$this->attrs[] = 0x00280101;
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
	function getDisplayAttrs() {
		$attrs = array();
		foreach ($this->attrs as $attr) {
			// skip displaying Study UID and Series UID
			if ($attr != 0x0020000d && $attr != 0x0020000e)
				$attrs[] = $attr;
		}
		return $attrs;
	}
}

class CMoveIdentifierPatient extends BaseObject {
    var $attrs = array();
    var $data;

    function __construct($patientid) {
        // add Query/Retrieve Level
        $level = 'PATIENT';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
        // add Patient ID
        $length = strlen($patientid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x10, 0x20, $length, $patientid);
		$this->attrs[] = 0x00100020;
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
}

class CMoveIdentifierStudy extends BaseObject {
    var $attrs = array();
    var $data;

    function __construct($patientid, $uid) {
        // add Query/Retrieve Level
        $level = 'STUDY';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
        // add Patient ID
        $length = strlen($patientid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x10, 0x20, $length, $patientid);
		$this->attrs[] = 0x00100020;
		// add Study Instance UID
        $length = strlen($uid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2Va' . $length, 0x20, 0xd, $length, $uid);
		$this->attrs[] = 0x0020000d;
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
}

class CMoveIdentifierSeries extends BaseObject {
    var $attrs = array();
    var $data;

    function __construct($patientid, $studyuid, $uid) {
        // add Query/Retrieve Level
        $level = 'SERIES';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
        // add Patient ID
        $length = strlen($patientid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x10, 0x20, $length, $patientid);
		$this->attrs[] = 0x00100020;
		// add Study Instance UID
        $length = strlen($studyuid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2Va' . $length, 0x20, 0xd, $length, $studyuid);
		$this->attrs[] = 0x0020000d;
		// add Series Instance UID
        $length = strlen($uid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2Va' . $length, 0x20, 0xe, $length, $uid);
		$this->attrs[] = 0x0020000e;
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
}

class CMoveIdentifierImage extends BaseObject {
    var $attrs = array();
    var $data;

    function __construct($patientid, $studyuid, $seriesuid, $uid) {
		// add SOP Instance UID
        $length = strlen($uid);
        if ($length & 0x1)
            $length++;
        $this->data = pack('v2Va' . $length, 0x8, 0x18, $length, $uid);
		$this->attrs[] = 0x00080018;
        // add Query/Retrieve Level
        $level = 'IMAGE';
        $length = strlen($level);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x8, 0x52, $length, $level);
		$this->attrs[] = 0x00080052;
        // add Patient ID
        $length = strlen($patientid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2VA' . $length, 0x10, 0x20, $length, $patientid);
		$this->attrs[] = 0x00100020;
		// add Study Instance UID
        $length = strlen($studyuid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2Va' . $length, 0x20, 0xd, $length, $studyuid);
		$this->attrs[] = 0x0020000d;
		// add Series Instance UID
        $length = strlen($seriesuid);
        if ($length & 0x1)
            $length++;
        $this->data .= pack('v2Va' . $length, 0x20, 0xe, $length, $seriesuid);
		$this->attrs[] = 0x0020000e;
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
}

class WorklistFindIdentifier extends BaseObject {
    var $attrs = array();
    var $data;
    var $optionalKeys;

    function __construct($patientid,
	                      $patientname,
						  $station,
						  $startdate,
						  $starttime,
						  $modality,
						  $referdoc) {
        $this->optionalKeys = true;
        // add Accession Number
        $this->data = pack('v2V', 0x8, 0x50, 0);
		$this->attrs[] = 0x00080050;
        if ($this->optionalKeys) {
            // add Institution Name
            $this->data .= pack('v2V', 0x8, 0x80, 0);
		    $this->attrs[] = 0x00080080;
        }
        // add Referring Physician's Name
        $this->data .= pack('v2V', 0x8, 0x90, 0);
		$this->attrs[] = 0x00080090;
        if ($this->optionalKeys) {
            // add Admitting Diagnosis
            $this->data .= pack('v2V', 0x8, 0x1080, 0);
		    $this->attrs[] = 0x00081080;
        }
        // add Referenced Study Sequence
        $this->data .= pack('v2V', 0x8, 0x1110, 0);
		$this->attrs[] = 0x00081110;
        // add Referenced Patient Sequence
        $this->data .= pack('v2V', 0x8, 0x1120, 0);
		$this->attrs[] = 0x00081120;
		// add Patient Name
		$length = strlen($patientname);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x10, 0x10, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $patientname);
		$this->attrs[] = 0x00100010;
        // add Patient ID
		$length = strlen($patientid);
		if ($length & 0x1)
			$length++;
        $this->data .= pack('v2V', 0x10, 0x20, $length);
		if ($length)
        	$this->data .= pack('A' . $length, $patientid);
		$this->attrs[] = 0x00100020;
        // add Patient's Birth Date
        $this->data .= pack('v2V', 0x10, 0x30, 0);
		$this->attrs[] = 0x00100030;
        // add Patient's Sex
        $this->data .= pack('v2V', 0x10, 0x40, 0);
		$this->attrs[] = 0x00100040;
        if ($this->optionalKeys) {
            // add Additional Patient History
            $this->data .= pack('v2V', 0x10, 0x21b0, 0);
		    $this->attrs[] = 0x001021b0;
        }
        // add Study Instance UID
        $this->data .= pack('v2V', 0x20, 0xd, 0);
		$this->attrs[] = 0x0020000d;
        // add Requesting Physician's Name
        $this->data .= pack('v2V', 0x32, 0x1032, 0);
		$this->attrs[] = 0x00321032;
        // add Requested Procedure Description
        $this->data .= pack('v2V', 0x32, 0x1060, 0);
		$this->attrs[] = 0x00321060;
		// build Requested Procedure Code Sequence
        // add Code Value
        $data = pack('v2V', 0x8, 0x100, 0);
		$this->attrs[] = 0x00080100;
        // add Coding Scheme Designator
        $data .= pack('v2V', 0x8, 0x102, 0);
		$this->attrs[] = 0x00080102;
        // add Coding Scheme Version
        $data .= pack('v2V', 0x8, 0x103, 0);
		$this->attrs[] = 0x00080103;
        // add Code Meaning
        $data .= pack('v2V', 0x8, 0x104, 0);
		$this->attrs[] = 0x00080104;
		// add Requested Procedure Code Sequence
        $item = pack('v2V', 0xFFFE, 0xE000, strlen($data));
		$item .= $data;
        $this->data .= pack('v2V', 0x32, 0x1064, strlen($item));
		$this->data .= $item;
		$this->attrs[] = 0x00321064;
		// build Scheduled Procedure Step Sequence
        // add Modality 
		$length = strlen($modality);
		if ($length & 0x1)
			$length++;
        $data = pack('v2V', 0x8, 0x60, $length);
		if ($length)
        	$data .= pack('A' . $length, $modality);
		$this->attrs[] = 0x00080060;
        // add Requested Contrast Agent
        $data .= pack('v2V', 0x32, 0x1070, 0);
		$this->attrs[] = 0x00321070;
		// add Scheduled Station AE Title 
		$length = strlen($station);
		if ($length & 0x1)
			$length++;
        $data .= pack('v2V', 0x40, 0x1, $length);
		if ($length)
        	$data .= pack('A' . $length, $station);
		$this->attrs[] = 0x00400001;
		// add Scheduled Procedure Step Start Date 
		$length = strlen($startdate);
		if ($length & 0x1)
			$length++;
        $data .= pack('v2V', 0x40, 0x2, $length);
		if ($length)
        	$data .= pack('A' . $length, $startdate);
		$this->attrs[] = 0x00400002;
		// add Scheduled Procedure Step Start Time 
		$length = strlen($starttime);
		if ($length & 0x1)
			$length++;
        $data .= pack('v2V', 0x40, 0x3, $length);
		if ($length)
        	$data .= pack('A' . $length, $starttime);
		$this->attrs[] = 0x00400003;
		// add Scheduled Performing Physician's Name
        $data .= pack('v2V', 0x40, 0x6, 0);
		$this->attrs[] = 0x00400006;
		// add Scheduled Protocol Code Sequence
		$this->attrs[] = 0x00400008;
		// add Scheduled Procedure Step Sequence
        $item = pack('v2V', 0xFFFE, 0xE000, strlen($data));
		$item .= $data;
        $this->data .= pack('v2V', 0x40, 0x100, strlen($item));
		$this->data .= $item;
		$this->attrs[] = 0x00400100;
        // add Requested Procedure ID
        $this->data .= pack('v2V', 0x40, 0x1001, 0);
		$this->attrs[] = 0x00401001;
        // add Requested Procedure Priority
        $this->data .= pack('v2V', 0x40, 0x1003, 0);
		$this->attrs[] = 0x00401003;
    }
    function __destruct() { }
    function getDataBuffer() {
        return $this->data;
    }
}

class ProtocolDataTfPdu extends BaseObject {
    var $ctxId;
    var $pdv;
    var $data;

    function __construct($ctxId) {
        $this->ctxId = $ctxId;
    }
    function __destruct() { }
    function getDataBuffer() {
        $header = pack('CCN', 0x4, 0, strlen($this->data));
        return ($header . $this->data);
    }
    function sendCommandEcho() {
        $this->pdv = new CEchoPdv();
        $data = $this->pdv->getDataBuffer();
        $this->data = pack('N', strlen($data)+2);
        $this->data .= pack('C2', $this->ctxId, 0x3);
        $this->data .= $data;
        #pacsone_dump($this->data);
    }
    function sendCommandFind($sopClass) {
        $this->pdv = new CFindPdv($sopClass);
        $data = $this->pdv->getDataBuffer();
        $this->data = pack('N', strlen($data)+2);
        $this->data .= pack('C2', $this->ctxId, 0x3);
        $this->data .= $data;
        #pacsone_dump($this->data);
    }
    function sendCommandMove($sopClass, $dest) {
        $this->pdv = new CMovePdv($sopClass, $dest);
        $data = $this->pdv->getDataBuffer();
        $this->data = pack('N', strlen($data)+2);
        $this->data .= pack('C2', $this->ctxId, 0x3);
        $this->data .= $data;
        #pacsone_dump($this->data);
    }
    function sendCommandWorklistFind() {
        $this->pdv = new WorklistFindPdv();
        $data = $this->pdv->getDataBuffer();
        $this->data = pack('N', strlen($data)+2);
        $this->data .= pack('C2', $this->ctxId, 0x3);
        $this->data .= $data;
        #pacsone_dump($this->data);
    }
    function sendCommandGetPrinter() {
        $this->pdv = new GetPrinterPdv();
        $data = $this->pdv->getDataBuffer();
        $this->data = pack('N', strlen($data)+2);
        $this->data .= pack('C2', $this->ctxId, 0x3);
        $this->data .= $data;
        #pacsone_dump($this->data);
    }
    function sendDataSet(&$dataset) {
        $data = $dataset->getDataBuffer();
        $this->data = pack('N', strlen($data)+2);
        $this->data .= pack('C2', $this->ctxId, 0x2);
        $this->data .= $data;
    }
    function recvResponse(&$data, &$complete, &$error) {
        return $this->pdv->recvResponse($data, $complete, $error);
    }
}

class AssociateReleasePdu extends BaseObject {
    var $data;

    function __construct() {
        $this->data = pack('N', 0);
    }
    function __destruct() { }
    function getDataBuffer() {
        $header = pack('CCN', 0x5, 0, strlen($this->data));
        return ($header . $this->data);
    }
}

class Association extends BaseObject {
    var $socket;
    var $ipAddr;
    var $hostName;
    var $tcpPort;
    var $calledAe;
    var $callingAe;
    var $accepted;
	var $connected = false;
    // constant definitions
    var $TIMEOUT = 60;

    function __construct($ip, $host, $port, $called, $calling) {
        $address = (strlen($ip))? $ip : $host;
        $errno = 0;
        $errstr = '';
        if (!empty($_REQUEST['dicom_debug'])) {
        	var_dump(array(
        		'address' => $address,
        		'port' => $port
			));
		}
        @$this->socket = fsockopen($address, $port, $errno, $errstr, $this->TIMEOUT);
		if (!empty($_REQUEST['dicom_debug'])) {
			var_dump(array(
				'errno' => $errno,
				'errstr' => $errstr
			));
		}
        if (!$this->socket)
			return false;
		else {
//            die ('<font color=red>fsockopen() failed: ' . $errstr . '(' . $errno . ')</font>');
			stream_set_timeout($this->socket, $this->TIMEOUT);
			$this->ipAddr = $ip;
			$this->hostName = $host;
			$this->tcpPort = $port;
			$this->calledAe = $called;
			$this->callingAe = $calling;
			$this->connected = true;
			$this->accepted = false;
		}
    }
    function __destruct() {
		if ($this->connected) {
			// close the association
        	fclose($this->socket);
		}
    }
	// establish association
	function associate($sopClass, &$error) {
		
		if (!$this->socket) {
			$error = 'PACS-сервер не отвечает. Повторите попытку позже';
            return false;
		}
	    $request = new AssociateRequestPdu($sopClass, $this->calledAe, $this->callingAe);
        $data = $request->getDataBuffer();
        fwrite($this->socket, $data, strlen($data));
        // check if request is accepted
        $data = fread($this->socket, 6);
		#pacsone_dump($data);
        // check the PDU type
        if ($data && ord($data[0]) == 0x2) {
            $array = unpack('Ctype/Cdummy/Nlength', $data);
			$pduLen = $array['length'];
        	$data = fread($this->socket, $pduLen);
			while (strlen($data) < $pduLen) {
        		$data .= fread($this->socket, $pduLen);
			}
			#pacsone_dump($data);
           	$this->accepted = $request->isAccepted($data, $error);
        }
        else {
            $error = 'Association request rejected';
            return false;
        }
    	return $request;
	}
	// release association
	function release(&$error) {
		if ($this->accepted) {
			// send ASSOCIATE_RELEASE_RQ PDU
	       	$request = new AssociateReleasePdu();
       		$data = $request->getDataBuffer();
		    fwrite($this->socket, $data, strlen($data));
			// check received ASSOCIATE_RELEASE_RP PDU
   		    $data = fread($this->socket, 10);
            if (strlen($data)) {
			    $pduType = ord($data[0]);
                /*
   	     	    if ($pduType != 0x6)
           		    $error .= "Invalid ASSOCIATE_RELEASE_RSP PDU received: pduType = " . $pduType;
                */
            }
		}
	}
    // send C-ECHO request
    function verify(&$error) {
        $result = false;
        global $C_ECHO;
        $sopClass = array($C_ECHO);
		$request = $this->associate($sopClass, $error);
		if (!$request)
			return false;
        // send the C-ECHO command PDV
        $dataPdu = new ProtocolDataTfPdu( $request->getPresentContextId() );
        $dataPdu->sendCommandEcho();
        $data = $dataPdu->getDataBuffer();
        fwrite($this->socket, $data, strlen($data));
        // read the response for the C-ECHO command
        $data = fread($this->socket, 6);
        // check the PDU type
		$respComplete = false;
		do {
        	if ($data && ord($data[0]) == 0x4) {
            	$array = unpack('Ctype/Cdummy/Nlength', $data);
		    	$pduLen = $array['length'];
            	$data = fread($this->socket, $pduLen);
		    	while (strlen($data) < $pduLen) {
            		$data .= fread($this->socket, $pduLen);
		    	}
       	    	$result = $dataPdu->recvResponse($data, $respComplete, $error);
        	}
        } while (!$respComplete && !strlen($error));
		// release the association
		$this->release($error);
        return $result;
    }
    // send C-FIND request
    function find(&$identifier, &$error) {
        global $C_FIND;
        global $C_FIND_STUDYROOT;
		
		if ( defined( 'C_FIND' ) )
			$C_FIND = C_FIND;
		if ( defined( 'C_FIND_STUDYROOT') )
			$C_FIND_STUDYROOT = C_FIND_STUDYROOT;
		
        $result = array();
        $sopClass = array($C_FIND, $C_FIND_STUDYROOT);
        $request = $this->associate($sopClass, $error);
        if (!$request)
            return false;
        // send the C-FIND command PDV
        $ctxId = $request->getPresentContextId();
        // remote AE has chosen the Study-Root Informational Model
        if (is_a($identifier, 'CFindIdentifierRoot') &&
            !strcasecmp($request->getSopClass($ctxId), $C_FIND_STUDYROOT)) {
            $identifier->studyRoot();
        }
        $dataPdu = new ProtocolDataTfPdu($ctxId);
        $dataPdu->sendCommandFind( $request->getSopClass($ctxId) );
        $data = $dataPdu->getDataBuffer();
        fwrite($this->socket, $data, strlen($data));
        // send the Identifier in a Data Set PDV 
        $dataPdu->sendDataSet($identifier);
        $data = $dataPdu->getDataBuffer();
        fwrite($this->socket, $data, strlen($data));
        // read the response for the C-FIND command
		$respComplete = false;
        do {
            $data = fread($this->socket, 6);
            // check the PDU type
            if ($data && ord($data[0]) == 0x4) {
                $array = unpack('Ctype/Cdummy/Nlength', $data);
		        $pduLen = $array['length'];
                $data = fread($this->socket, $pduLen);
		        while (strlen($data) < $pduLen) {
                	$data .= fread($this->socket, $pduLen);
		        }
       	        $match = $dataPdu->recvResponse($data, $respComplete, $error);
				if (!$match)
					break;
				if (is_a($match, 'CMatchResult'))
				    $result[] = $match;
            }
        } while (!$respComplete && !strlen($error));
		// release the association
		$this->release($error);
        return $result;
    }
    // send C-MOVE request
    function move($dest, &$identifier, &$error) {
        $result = array();
        global $C_MOVE;
        global $C_MOVE_STUDYROOT;
		if ( defined( 'C_MOVE' ) )
			$C_MOVE = C_MOVE;
		if ( defined( 'C_MOVE_STUDYROOT') )
			$C_MOVE_STUDYROOT = C_MOVE_STUDYROOT;
		
        $sopClass = array($C_MOVE, $C_MOVE_STUDYROOT);
		$request = $this->associate($sopClass, $error);
        if (!$request)
			return false;
        // send the C-MOVE command PDV
        $ctxId = $request->getPresentContextId();
        $dataPdu = new ProtocolDataTfPdu($ctxId);
        $dataPdu->sendCommandMove($request->getSopClass($ctxId), $dest);
        $data = $dataPdu->getDataBuffer();
        fwrite($this->socket, $data, strlen($data));
        // send the Identifier in a Data Set PDV 
        $dataPdu->sendDataSet($identifier);
        $data = $dataPdu->getDataBuffer();
        fwrite($this->socket, $data, strlen($data));
        // read the response for the C-MOVE command
		$respComplete = false;
        do {
            $data = fread($this->socket, 6);
            // check the PDU type
            if ($data && ord($data[0]) == 0x4) {
                $array = unpack('Ctype/Cdummy/Nlength', $data);
		        $pduLen = $array['length'];
                $data = fread($this->socket, $pduLen);
		        while (strlen($data) < $pduLen) {
                	$data .= fread($this->socket, $pduLen);
		        }
       	        $match = $dataPdu->recvResponse($data, $respComplete, $error);
				if (!$match)
					break;
				if (is_a($match, 'CFailedSopInstances'))
					$result[] = $match;
            }
        } while (!$respComplete && !strlen($error));
		// release the association
		$this->release($error);
        return $result;
    }
    // send Modality Worklist-FIND request
    function findWorklist(&$identifier, &$error) {
        $result = array();
        global $WORKLIST_FIND;
        $sopClass = array($WORKLIST_FIND);
		$request = $this->associate($sopClass, $error);
        if (!$request)
			return false;
        // send the Modality Worklist-FIND command PDV
        $dataPdu = new ProtocolDataTfPdu( $request->getPresentContextId() );
        $dataPdu->sendCommandWorklistFind();
        $data = $dataPdu->getDataBuffer();
        fwrite($this->socket, $data, strlen($data));
        // send the Identifier in a Data Set PDV 
        $dataPdu->sendDataSet($identifier);
        $data = $dataPdu->getDataBuffer();
        fwrite($this->socket, $data, strlen($data));
        // read the response for the Modality Worklist-FIND command
		$respComplete = false;
        do {
            $data = fread($this->socket, 6);
            // check the PDU type
            if ($data && ord($data[0]) == 0x4) {
                $array = unpack('Ctype/Cdummy/Nlength', $data);
		        $pduLen = $array['length'];
                $data = fread($this->socket, $pduLen);
		        while (strlen($data) < $pduLen) {
                	$data .= fread($this->socket, $pduLen);
		        }
       	        $match = $dataPdu->recvResponse($data, $respComplete, $error);
				if (is_a($match, 'Item'))
				    $result[] = $match;
            }
        } while (!$respComplete && !strlen($error));
		// release the association
		$this->release($error);
        return $result;
    }
    // send N-GET command to get printer properties
    function getPrinter(&$error) {
        $result = array();
        global $BASIC_PRINT;
        $sopClass = array($BASIC_PRINT);
		$request = $this->associate($sopClass, $error);
        if (!$request)
			return false;
        // send the N-GET command PDV
        $dataPdu = new ProtocolDataTfPdu( $request->getPresentContextId() );
        $dataPdu->sendCommandGetPrinter();
        $data = $dataPdu->getDataBuffer();
        fwrite($this->socket, $data, strlen($data));
        // read the response for the Modality Worklist-FIND command
		$respComplete = false;
        do {
            $data = fread($this->socket, 6);
            // check the PDU type
            if ($data && ord($data[0]) == 0x4) {
                $array = unpack('Ctype/Cdummy/Nlength', $data);
		        $pduLen = $array['length'];
                $data = fread($this->socket, $pduLen);
		        while (strlen($data) < $pduLen) {
                	$data .= fread($this->socket, $pduLen);
		        }
       	        $match = $dataPdu->recvResponse($data, $respComplete, $error);
				if (is_a($match, 'CAttributeList'))
				    $result[] = $match;
            }
        } while (!$respComplete && !strlen($error));
		// release the association
		$this->release($error);
        return $result[0];
    }
}

class StructuredReport extends DicomObject {
    var $attrs = array();
    var $root;
    var $explicit = false;
    var $keyObj = false;

    function __construct($path) {
		global $ATTR_TBL;
		global $EXPLICIT_VR_TBL;
        global $XFER_SYNTAX_TBL;
        global $UNDEF_LEN;
		$handle = fopen($path, "rb");
		$data = fread($handle, filesize($path));
		fclose($handle);
		// check if Part 10 format
		$signature = substr($data, 128, 4);
		if (strcmp($signature, "DICM") == 0) {
			// skip the Part 10 headers
			$data = substr($data, 128+4);
        	$array = unpack('vgroup/velement/A2vr/vvalue/Vlength', $data);
			$group = $array['group'];
			$element = $array['element'];
			$vr = $array['vr'];
			$value = isset($array['value'])? $array['value'] : "";
			$length = $array['length'];
            $metaLen = 2+2+2+2+4;
            $meta = substr($data, $metaLen, $length);
            $data = substr($data, $metaLen+$length);
            // check the transfer syntax
            while (strlen($meta) > 0) {
  	            $array = unpack('vgroup/velement/A2vr', $meta);
                $group = $array['group'];
                $element = $array['element'];
                $vr = $array['vr'];
                $meta = substr($meta, 6);
                if (isset($EXPLICIT_VR_TBL[ $vr ])) {
                    $format = "vreserved/Vlength";
                    $offset = 6;
                } else {
                    $format = "vlength";
                    $offset = 2;
                }
      	        $array = unpack($format, $meta);
                $length = $array['length'];
                // check transfer syntax to see if it's supported
                if (($group == 0x0002) && ($element == 0x0010)) {
                    $uid = substr($meta, $offset, $length);
                    $format = "a" . $length . "uid";
                    $array = unpack($format, $uid);
                    $uid = $array['uid'];
                    #print "Dicom Transfer Syntax: <b>$uid</b><br>";
                    if (isset($XFER_SYNTAX_TBL[$uid])) {
                        $this->explicit = $XFER_SYNTAX_TBL[$uid][0];
                        $this->bigEnd = $XFER_SYNTAX_TBL[$uid][1];
                    }
                }
                $meta = substr($meta, $offset+$length);
            }
		}
		while (strlen($data) > 0) {
            $header = substr($data, 0, ($this->explicit)? 6 : 8);
            $headerLen = strlen($header);
            if ($this->explicit) {
                $format = $this->bigEnd? 'ngroup/nelement/A2vr' : 'vgroup/velement/A2vr';
            } else {
                $format = $this->bigEnd? 'ngroup/nelement/Nlength' : 'vgroup/velement/Vlength';
            }
        	$array = unpack($format, $header);
			$group = $array['group'];
			$element = $array['element'];
            $body = substr($data, $headerLen);
            $vr = "";
            if ($this->explicit) {
                $vr = $array['vr'];
                if (isset($EXPLICIT_VR_TBL[$vr])) {
                    $format = $this->bigEnd? "nreserved/Nlength" : "vreserved/Vlength";
                    $offset = 6;
                } else {
                    $format = $this->bigEnd? "nlength" : "vlength";
                    $offset = 2;
                }
                $header = substr($body, 0, $offset);
                $body = substr($body, $offset);
                $headerLen += $offset;
      	        $array = unpack($format, $header);
                $length = $array['length'];
            } else {
                $length = $array['length'];
            }
			$key = $group << 16 | $element;
			if (($length == $UNDEF_LEN) || (strcmp($vr, "SQ") == 0)) {	// sequence elements
                if ($length == $UNDEF_LEN)
                    $length = strlen($body);
				$this->attrs[$key] = new Sequence($key, $length, $body, $this->explicit, $this->bigEnd);
				$length = $this->attrs[$key]->getLength();
			} else {
				if (isset($ATTR_TBL[$key]))
					$format = $this->convertEndian($ATTR_TBL[$key]->type);
				else
					$format = "A";
				if (strcasecmp($format[0], "A") == 0)
					$format .= $length;
				$format .= 'value';
				$array = unpack($format, $body);
			    $value = isset($array['value'])? $array['value'] : "";
				// save this attribute
				$this->attrs[$key] = $value;
			}
			$data = substr($data, $headerLen+$length);
		}
		$this->classify();
	}
    function __destruct() {}
    function showDebug() {
		foreach ($this->attrs as $key => $attr) {
        	if (is_a($attr, 'Sequence'))
				$attr->showDebug();
			else
				print "Attribute (" . dechex($key) . "): Value = $attr<br>";
		}
	}
	function classify() {
		// find root content item
		if (!isset($this->attrs[0x0040A040]) ||
			strcasecmp($this->attrs[0x0040A040], "Container")) {
            print "<font color=red>Failed to find Root Content Item:<br>";
			$this->showDebug();
			print "</font>";
			return;
		}
		$concept = $this->attrs[0x0040A043];
		$continuity = $this->attrs[0x0040A050];
		$this->root = new ContainerItem($concept, $continuity);
		// build the rest of the content tree
		$this->root->buildContentTree($this->attrs[0x0040A730]);
        if (array_key_exists(0x0040A375, $this->attrs) &&
            !$this->attrs[0x0040A375]->isEmpty())
            $this->keyObj = new KeyObjectSelectionItem($this->attrs[0x0040A375]);
	}
	function showHtml() {
		$this->root->showHtml();
        if ($this->keyObj) {
            print "<h3>";
            print pacsone_gettext("Key Object Selection Module Attributes");
            print "</h3>";
            $this->keyObj->showHtml();
        }
	}
}

// key object selection classes
class ReferencedSeriesItem extends BaseObject {
    var $seriesUid;
    var $retAetitle;
    var $refInstances = array();

    function __construct(&$attr) {
        $this->seriesUid = $attr->getAttr(0x0020000E);
        $this->retAeTitle = $attr->getAttr(0x00080054);
        $instances = $attr->getItem(0x00081199);
        foreach ($instances->items as $item) {
            $this->refInstances[] = array("class"       => $item->getAttr(0x00081150),
                                          "instance"    => $item->getAttr(0x00081155));
        }
    }
    function __destruct() {}
	function showHtml() {
		die( get_class().'->'.__FUNCTION__ );
        global $dbcon;
        $uid = $this->seriesUid;
		$url = $dbcon->findRefSeries($uid);
		if (strlen($url))
			$uid = $url;
        print pacsone_gettext("Series Instance UID: ") . $uid . "<br>";
        print pacsone_gettext("Retrieve AE Title: ") . $this->retAeTitle . "<br>";
        print "<ul>";
        foreach ($this->refInstances as $instance) {
            print "<li>";
            print pacsone_gettext("Referenced SOP Class UID: ") . $instance["class"] . "<br>";
            $uid = $instance["instance"];
		    $url = $dbcon->findRefImage($uid);
		    if (strlen($url))
			    $uid = $url;
            print pacsone_gettext("Referenced SOP Instance UID: ") . $uid . "<br>";
            print "</li>";
        }
        print "</ul>";
    }
}

class KeyObjectSelectionItem extends BaseObject {
    var $studyUid;
    var $refSeries = array();

    function __construct(&$attr) {
        $this->studyUid = $attr->getAttr(0x0020000D);
        $series = $attr->getItem(0x00081115);
        foreach ($series->items as $item) {
            $this->refSeries[] = new ReferencedSeriesItem($item);
        }
    }
    function __destruct() {}
	function showHtml() {
		die( get_class().'->'.__FUNCTION__ );
        global $dbcon;
        $uid = $this->studyUid;
        print "<p><b>";
		$url = $dbcon->findRefStudy($uid);
		if (strlen($url))
			$uid = $url;
        print pacsone_gettext("Study Instance UID: ") . $uid;
        print "</b><br>";
        print "<ul>";
        foreach ($this->refSeries as $series) {
            print "<li>";
            $series->showHtml();
            print "</li>";
        }
        print "</ul>";
    }
}

class ContentItem extends BaseObject {
	var $type;
	var $conceptNameCode;
	var $value;
	var $children;
	var $observationDatetime;
	// relationship types
	var $contains = array();
	var $obsContexts = array();
	var $conceptMods = array();
	var $properties = array();
	var $acqContexts = array();
	var $inferred = array();
	var $selected = array();

    function __construct(&$concept) {
		if (isset($concept) && $concept->hasKey(0x00080104))
			$this->conceptNameCode = $concept->getAttr(0x00080104);
		else
			$this->conceptNameCode = "Content Item";
		$this->children = 0;
		$this->observationDatetime = "";
	}
    function __destruct() {}
	function buildContentTree(&$content) {
		$this->children = count($content->items);
		foreach ($content->items as $item) {
			if ($item->hasKey(0x0040A010) && $item->hasKey(0x0040A040)) {
				$relation = $item->getAttr(0x0040A010);
				$type = $item->getAttr(0x0040A040);
				if (strcasecmp($type, "CONTAINER") == 0) {
                    $heading = null;
					if (isset($item->attrs[0x0040A043]))
						$heading = $item->attrs[0x0040A043];
					$contentItem = new ContainerItem($heading, $item->getAttr(0x0040A050));
				}
				else if (isset($item->attrs[0x0040A043])) {
					$concept = $item->attrs[0x0040A043];
					switch ($type) {
					case "TEXT":
						$value = $item->getAttr(0x0040A160);
						$contentItem = new TextValueItem($concept, $value);
						break;
					case "DATETIME":
						$value = $item->getAttr(0x0040A120);
						$contentItem = new DateTimeItem($concept, $value);
					break;
					case "DATE":
						$value = $item->getAttr(0x0040A121);
						$contentItem = new DateItem($concept, $value);
						break;
					case "TIME":
						$value = $item->getAttr(0x0040A122);
						$contentItem = new TimeItem($concept, $value);
						break;
					case "PNAME":
						$value = $item->getAttr(0x0040A123);
						$contentItem = new PersonNameItem($concept, $value);
						break;
					case "UIDREF":
						$value = $item->getAttr(0x0040A124);
						$contentItem = new UidItem($concept, $value);
						break;
					case "NUM":
						$value = $item->attrs[0x0040A300];
						$contentItem = new NumericItem($concept, $value);
						break;
					case "CODE":
						$value = $item->attrs[0x0040A168];
						$contentItem = new CodeItem($concept, $value);
						break;
					case "COMPOSITE":
						$value = $item->attrs[0x00081199];
						$contentItem = new CompositeItem($concept, $value);
						break;
					case "IMAGE":
						if (isset($item->attrs[0x00081199]))
							$value = $item->attrs[0x00081199];
						$contentItem = new ImageItem($concept, $value);
						break;
					case "WAVEFORM":
						if (isset($item->attrs[0x00081199]))
							$value = $item->attrs[0x00081199];
						$contentItem = new WaveformItem($concept, $value);
						break;
					default:
						break;
					}
				}
				if (isset($contentItem)) {
					// set observation datetime if present
					if ($item->hasKey(0x0040A032))
						$contentItem->observationDatetime = $item->getAttr(0x0040A032);
					// recursively build the sub-content tree
					if (isset($item->attrs[0x0040A730])) {
						$contentItem->buildContentTree($item->attrs[0x0040A730]);
					}
					// categorize to each relationship list
					switch ($relation) {
					case "CONTAINS":
						$this->contains[] = $contentItem;
						break;
					case "HAS OBS CONTEXT":
						$this->obsContexts[] = $contentItem;
						break;
					case "HAS CONCEPT MOD":
						$this->conceptMods[] = $contentItem;
						break;
					case "HAS PROPERTIES":
						$this->properties[] = $contentItem;
						break;
					case "HAS ACQ CONTEXT":
						$this->acqContexts[] = $contentItem;
						break;
					case "INFERRED FROM":
						$this->inferred[] = $contentItem;
						break;
					case "SELECTED FROM":
						$this->selected[] = $contentItem;
						break;
					default:
						break;
					}
				}
			}
		}
	}
	function showHtml() {
		$this->showMeHtml();
		$lists = array (
			"Has Observation Context"		=> $this->obsContexts,
			"Contains"						=> $this->contains,
			"Has Concept Modifier"			=> $this->conceptMods,
			"Has Properties"				=> $this->properties,
			"Has Acquisition Context"		=> $this->acqContexts,
			"Inferred From"				=> $this->inferred,
			"Selected From"				=> $this->selected
		);
		foreach ($lists as $name => $list) {
			if (!count($list))
				continue;
			print "<ul><b><u>$name</u></b><br>";
			foreach ($list as $node) {
				print "<li>";
				$node->showHtml();
				print "</li>";
			}
			print "</ul>";
		}
	}
	function showMeHtml() {
		print "<b>" . $this->conceptNameCode;
		if (strlen($this->observationDatetime))
			print " (<i>Observation DateTime</i>: " . $this->observationDatetime . ")";
		print "</b><br>" . $this->value . "<br>";
	}
}

class ContainerItem extends ContentItem {
	var $continuity;

    function __construct(&$concept, &$continuity) {
		ContentItem::__construct($concept);
		$this->type = "Container";
		$this->continuity = $continuity;
	}
    function __destruct() {}
	function showMeHtml() {
		print "<h2>" . $this->conceptNameCode . "</h2>";
	}
}

class TextValueItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept);
		$this->type = "TextValue";
		$this->value = $value;
	}
    function __destruct() {}
	function showMeHtml() {
		print "<b>" . $this->conceptNameCode;
        // convert '\r', '\n' or "\r\n" into HTML <br> tag
        if (strstr($this->value, "\r") || strstr($this->value, "\n")) {
            $this->value = str_replace("\r\n", "<br>", $this->value);
            $this->value = str_replace("\r", "<br>", $this->value);
            $this->value = str_replace("\n", "<br>", $this->value);
        }
		print "</b><br>" . $this->value . "<br>";
    }
}

class DateTimeItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept);
		$this->type = "DateTime";
		$this->value = $value;
	}
    function __destruct() {}
}

class DateItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept);
		$this->type = "Date";
		$this->value = $value;
	}
    function __destruct() {}
}

class TimeItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept);
		$this->type = "Time";
		$this->value = $value;
	}
    function __destruct() {}
}

class PersonNameItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept);
		$this->type = "PersonName";
		$this->value = $value;
	}
    function __destruct() {}
}

class UidItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept, $value);
		$this->type = "Uid";
		$this->value = $value;
	}
    function __destruct() {}
}

class NumericItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept, $value);
		$this->type = "Numeric";
		$this->value = $value;
	}
    function __destruct() {}
	function showMeHtml() {
		print "<b>" . $this->conceptNameCode . "</b><br>";
		$numeric = $this->value->getAttr(0x0040A30A);
		$code = $this->value->getItem(0x004008EA);
		$numeric .= " " . $code->getAttr(0x00080100);
		$numeric .= " (" . $code->getAttr(0x00080104) . ")";
		print "$numeric<br>";
	}
}

class CodeItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept, $value);
		$this->type = "Code";
		$this->value = $value;
	}
    function __destruct() {}
	function showMeHtml() {
		print "<b>" . $this->conceptNameCode . "</b><br>";
		$code = $this->value->getAttr(0x00080104);
		print "$code<br>";
	}
}

class CompositeItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept, $value);
		$this->type = "Composite";
		$this->value = $value;
	}
    function __destruct() {}
	function showMeHtml() {
		die( get_class().'->'.__FUNCTION__ );
        global $dbcon;
		print "<b>" . $this->conceptNameCode . "</b><br>";
		print "<table>";
		$uid = $this->value->getAttr(0x00081150);
		print "<tr><td>Referenced SOP Class: </td>";
		print "<td>" . getSopClassName($uid) . "</td></tr>";
		$uid = $this->value->getAttr(0x00081155);
		print "<tr><td>Referenced SOP Instance: </td>";
		$url = $dbcon->findRefImage($uid);
		if (strlen($url))
			$uid = $url;
		print "<td>" . $uid . "</td></tr>";
		print "</table><br>";
	}
}

class ImageItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept, $value);
		$this->type = "Image";
		$this->value = $value;
	}
    function __destruct() {}
	function showMeHtml() {
		die( get_class().'->'.__FUNCTION__ );
        global $dbcon;
		print "<b>" . $this->conceptNameCode . "</b><br>";
		print "<table>";
		$uid = $this->value->getAttr(0x00081150);
		print "<tr><td>Referenced SOP Class: </td>";
		print "<td>" . getSopClassName($uid) . "</td></tr>";
		$uid = $this->value->getAttr(0x00081155);
		print "<tr><td>Referenced SOP Instance: </td>";
		$url = $dbcon->findRefImage($uid);
		if (strlen($url))
			$uid = $url;
		print "<td>" . $uid . "</td></tr>";
		// optional reference frame number
		if ($this->value->hasKey(0x00081160)) {
			print "<tr><td>Referenced Frame Number: </td>";
			$refFrame = $this->value->getAttr(0x00081160);
			print "<td>" . $refFrame . "</td></tr>";
		}
		// optional reference to softcopy presentation statement SOP pair
		if ($this->value->hasKey(0x00081199)) {
			$prenState = $this->value->getItem(0x00081199);
			$uid = $prenState->getAttr(0x00081150);
			print "<tr><td>Referenced Softcopy Prensentation State SOP Class: </td>";
			print "<td>" . getSopClassName($uid) . "</td></tr>";
			$uid = $prenState->getAttr(0x00081155);
			print "<tr><td>Referenced Softcopy Prensentation State SOP Instance: </td>";
			$url = $dbcon->findRefImage($uid);
			if (strlen($url))
				$uid = $url;
			print "<td>" . $uid . "</td></tr>";
		}
		print "</table><br>";
	}
}

class WaveformItem extends ContentItem {
    function __construct(&$concept, &$value) {
		ContentItem::__construct($concept, $value);
		$this->type = "Waveform";
		$this->value = $value;
	}
    	function __destruct() {}
    	function showMeHtml() {
			die( get_class().'->'.__FUNCTION__ );
    	global $dbcon;
	print "<b>" . $this->conceptNameCode . "</b><br>";
	print "<table>";
	$uid = $this->value->getAttr(0x00081150);
	print "<tr><td>Referenced SOP Class: </td>";
	print "<td>" . getSopClassName($uid) . "</td></tr>";
	$uid = $this->value->getAttr(0x00081155);
	print "<tr><td>Referenced SOP Instance: </td>";
	$url = $dbcon->findRefImage($uid);
	if (strlen($url))
		$uid = $url;
	print "<td>" . $uid . "</td></tr>";
	print "<tr><td>Referenced Waveform Channels: </td>";
	if ($this->value->hasKey(0x0040A0B0))
		$channel = $this->value->getAttr(0x0040A0B0);
	else
		$channel = "N/A";
	print "<td>" . $channel . "</td></tr>";
	print "</table><br>";
	}
}

class RawTags extends DicomObject {
    var $attrs = array();
    var $explicit = false;
    var $handle;
    var $fileSize;
    var $syntax;

    function __construct($path) {
        global $XFER_SYNTAX_TBL;
        global $EXPLICIT_VR_TBL;
        $this->handle = fopen($path, "rb");
        $this->fileSize = filesize($path);
        $data = fread($this->handle, 132);
        // check if Part 10 format
        $signature = substr($data, 128, 4);
        if (strcmp($signature, "DICM") == 0) {
            $this->fileSize -= 132;
            // skip the Part 10 headers
            $data = fread($this->handle, 12);
            $this->fileSize -= 12;
            $array = unpack('vgroup/velement/A2vr/vvalue/Vlength', $data);
            $group = $array['group'];
            $element = $array['element'];
            $vr = $array['vr'];
            $value = isset($array['value'])? $array['value'] : "";
            $length = $array['length'];
            $headers = fread($this->handle, $length);
            $this->fileSize -= $length;
            while (strlen($headers) > 0) {
        	    $array = unpack('vgroup/velement/A2vr', $headers);
                $group = $array['group'];
                $element = $array['element'];
                $vr = $array['vr'];
                $headers = substr($headers, 6);
                if (isset($EXPLICIT_VR_TBL[ $vr ])) {
                    $format = "vreserved/Vlength";
                    $offset = 6;
                } else {
                    $format = "vlength";
                    $offset = 2;
                }
            	$array = unpack($format, $headers);
                $length = $array['length'];
                // check transfer syntax to see if it's supported
                if (($group == 0x0002) && ($element == 0x0010)) {
                    $uid = substr($headers, $offset, $length);
                    $format = "a" . $length . "uid";
                    $array = unpack($format, $uid);
                    $uid = $array['uid'];
                    $syntax = $XFER_SYNTAX_TBL[$uid][2];
                    $this->syntax = "$syntax - $uid";
                    if (isset($XFER_SYNTAX_TBL[$uid])) {
                        $this->explicit = $XFER_SYNTAX_TBL[$uid][0];
                        $this->bigEnd = $XFER_SYNTAX_TBL[$uid][1];
                    }
                }
                $headers = substr($headers, $offset+$length);
            }
        } else {
            fseek($this->handle, 0, SEEK_SET);
            // try to guess if the transfer syntax is explicit or implicit vr
            $vr = substr($data, 4, 2);
            if (ctype_alnum($vr))
                $this->explicit = true;
        }
        if ($this->explicit) {
            $this->parseDataExplicit();
        } else {
            $this->parseDataImplicit();
        }
    }
    function __destruct() {
        fclose($this->handle);
    }
    function parseDataImplicit() {
        global $ATTR_TBL;
        global $UNDEF_LEN;
        while ($this->fileSize > 0) {
            $header = fread($this->handle, 8);
            $this->fileSize -= 8;
            $format = $this->bigEnd? 'ngroup/nelement/Nlength' : 'vgroup/velement/Vlength';
        	$array = unpack($format, $header);
            $group = $array['group'];
            $element = $array['element'];
            $length = $array['length'];
            if ($length == 0)
                continue;
            $key = $group << 16 | $element;
            if ($key > 0x7FE00000)
                break;
            if ($length != $UNDEF_LEN) {
                $body = fread($this->handle, $length);
            } else {
                // must be a sequence
                $data = fread($this->handle, 2);
                $body = $data;
                $format = $this->bigEnd? 'ncurrent' : 'vcurrent';
                $array = unpack($format, $data);
                $current = $array['current'];
                $last = 0;
                while (($last != 0xFFFE) || ($current != 0xE0DD)) {
                    $last = $current;
                    $data = fread($this->handle, 2);
                    $body .= $data;
                    $array = unpack($format, $data);
                    $current = $array['current'];
                }
                // read the sequence delimiter length
                $body .= fread($this->handle, 4);
            }
            if (isset($ATTR_TBL[$key]))
                $format = $this->convertEndian($ATTR_TBL[$key]->type);
            else
                $format = ($element == 0)? $this->convertEndian("V") : "A";
            //print "Key = " . dechex($key) . " Length = $length Format = $format<br>";
            if ( ($length == $UNDEF_LEN) ||
                 (strcasecmp($format[0], "S") == 0) ) {     // sequence elements
                $this->attrs[$key] = new Sequence($key, strlen($body), $body, false, $this->bigEnd);
                $length = $this->attrs[$key]->getLength();
            } else {
                if (strcasecmp($format[0], "A") == 0)
                    $format .= $length;
                $format .= 'value';
                $array = unpack($format, $body);
                $value = isset($array['value'])? $array['value'] : "";
                // save this attribute
                $this->attrs[$key] = $value;
            }
            $this->fileSize -= $length;
        }
    }
    function parseDataExplicit() {
        global $ATTR_TBL;
        global $EXPLICIT_VR_TBL;
        global $UNDEF_LEN;
        while ($this->fileSize > 0) {
            $header = fread($this->handle, 6);
            $this->fileSize -= 6;
            $format = $this->bigEnd? 'ngroup/nelement/A2vr' : 'vgroup/velement/A2vr';
        	$array = unpack($format, $header);
            $group = $array['group'];
            $element = $array['element'];
            $key = $group << 16 | $element;  
            if ($key > 0x7FE00000)
                break;
            $vr = $array['vr'];
            if (isset($EXPLICIT_VR_TBL[$vr])) {
                $format = $this->bigEnd? "nreserved/Nlength" : "vreserved/Vlength";
                $offset = 6;
            } else {
                $format = $this->bigEnd? "nlength" : "vlength";
                $offset = 2;
            }
            $data = fread($this->handle, $offset);
            $this->fileSize -= $offset;
            $array = unpack($format, $data);
            $length = $array['length'];
            if ($length == 0)
                continue;
            if ($length != $UNDEF_LEN) {
                $body = fread($this->handle, $length);
            } else {
                // must be a sequence
                $data = fread($this->handle, 2);
                $body = $data;
                $format = $this->bigEnd? 'ncurrent' : 'vcurrent';
                $array = unpack($format, $data);
                $current = $array['current'];
                $last = 0;
                while (($last != 0xFFFE) || ($current != 0xE0DD)) {
                    $last = $current;
			        $data = fread($this->handle, 2);
                    $body .= $data;
                    $array = unpack($format, $data);
                    $current = $array['current'];
                }
                // read the sequence delimiter length
                $body .= fread($this->handle, 4);
            }
            if (isset($ATTR_TBL[$key]))
                $format = $this->convertEndian($ATTR_TBL[$key]->type);
            else
                $format = ($element == 0)? $this->convertEndian("V") : "A";
            //print "Key = " . dechex($key) . " Length = $length Format = $format<br>";
            if ( ($length == $UNDEF_LEN) ||
                 (strcasecmp($format[0], "S") == 0) ) {     // sequence elements
                if ($length == $UNDEF_LEN)
                    $length = strlen($body);
                $this->attrs[$key] = new Sequence($key, $length, $body, true, $this->bigEnd);
                $length = $this->attrs[$key]->getLength();
            } else {
                if (strcasecmp($format[0], "A") == 0)
                    $format .= $length;
                $format .= 'value';
                $array = unpack($format, $body);
                $value = isset($array['value'])? $array['value'] : "";
                // save this attribute
                $this->attrs[$key] = $value;
            }
            $this->fileSize -= $length;
        }
    }
    function showDebug() {
        foreach ($this->attrs as $key => $attr) {
        	if (is_a($attr, 'Sequence'))
                $attr->showDebug();
            else
                print "Attribute (" . dechex($key) . "): Value = $attr<br>";
        }
    }
    function showHtml() {
        global $ATTR_TBL;
        if (strlen($this->syntax))
            print "<p>Dicom Transfer Syntax: <b>" . $this->syntax . "</b><br>";
        print "<p><table width=100% cellpadding=3 cellspacing=0 border=1>\n";
        $columns = array("Tag", "Description", "Value");
        print "<tr align=center>";
        foreach ($columns as $key) {
            print "<td><b>$key</b></td>\n";
        }
        print "</tr>\n";
        foreach ($this->attrs as $key => $attr) {
            $group = ($key >> 16);
            $element = ($key & 0xffff);
            if (isset($ATTR_TBL[$key])) {
                $name = $ATTR_TBL[$key]->name;
                $format = $ATTR_TBL[$key]->type;
            } else {
                $name = ($element == 0)? "Group Length" : "Unknown";
                $format = ($element == 0)? "V" : "A";
            }
            $key = sprintf("%04x,%04x", $group, $element);
        	if (is_a($attr, 'Sequence'))
                $value = "Sequence";
            else {
                $value = trim($attr);
                if ($format[0] == 'A')
                    $value = str_replace("^", " ", $value);
            }
            print "<tr><td>$key</td>";
            print "<td>$name</td>\n";
            // do not display private tags
            if ($group & 1)
                $value = "Private Tag";
            if (!strlen($value))
                $value = "&nbsp;";
            if (strlen($value) > 0x100)
                $value =  substr($value, 0, 0x100) . "<br><b>(Only first 256 characters is shown)</b>";
            print "<td>" . htmlentities($value) . "</td></tr>\n";
        }
        print "</table>\n";
    }
}

?>
