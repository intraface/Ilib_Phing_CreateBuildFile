Create phing build file
==

Helps create phing build files. It was developed to easily create phing build files for the intraface public repositories located at public.intraface.dk.

From the command line, do the following.

    create-build-file

Troubleshooting
--

Problem: "undefined function PEAR_Config::getRemote()".
Solution: Remove support for mirrors in PEAR/PackageUpdate.php. Set $mirror = false on line 721. 

