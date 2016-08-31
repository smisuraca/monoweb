#!/usr/bin/perl

#
# Version 1 del script que actualiza el ftp de cinevivo. 
# 
# . Devuelve la lista que existe de flvs, y envia la cantidad de cortos que se le envien como parametro
#
use Net::FTP;
use Data::Dumper;


use POSIX qw(strftime);

my $time = time;
my $d = strftime('%Y%m%d', localtime($time));


open FH, "files_to_upgrade";
my @files;
while(<FH>) {
    $_ =~ s/\n//;
    if(-f $_) {
	push @files , $_;
    }
}
print Dumper(\@files);
#exit(0);

my $host="www.cinevivo.org";
my $directory="flvs";

my $text = "<script type='text/javascript' src='../js/ufo.js'></script><div id='player'></div><table><tr valign='top'><td><ul>";

    $ftp=Net::FTP->new($host,Timeout=>240) or $newerr=1;
    push @ERRORS, "Can't ftp to $host: $!\n" if $newerr;
    myerr() if $newerr;
    print "Connected\n";

    $ftp->login("1410771\@aruba.it","3goshushijo8") or $newerr=1;
#    print "Getting file list";
    push @ERRORS, "Can't login to $host: $!\n" if $newerr;
    $ftp->quit if $newerr;
    myerr() if $newerr;
    print "\nLogged in\n";
#
#    $ftp->cwd($directory) or $newerr=1;
#    push @ERRORS, "Can't cd  $!\n" if $newerr;
#    myerr() if $newerr;

    $ftp->cwd("www.cinevivo.org") or die "No puedo pararme en cinevivo.org";

    foreach my $file (@files) {
	my @files_server;
	
	my @folders = split("/", $file);	
	my $count = scalar(@folders);
	my $i;
	my $folder="./";
	for ($i=0; $i<$count-1; $i++) {
	    $folder.=@folders->[$i]."/";
	    $folder_back.="../";
	}
	my $file_only = @folders->[$count-1];

	$ftp->cwd($folder) or $newerr=1;
        push @ERRORS, "Can't cd $folder $!\n" if $newerr;
        myerr() if $newerr;
        
#        @files_server=$ftp->dir or $newerr=1;
#        push @ERRORS, "Can't get file list $!\n" if $newerr;
#        myerr() if $newerr;

                      
#        print Dumper(@files_server);
	print "\n\n.... [ $folder$file_only ] ....\n";

	if($ftp->size($file_only)) {
	    print "[BACKUP OK] $folder$file_only".".$d \n";
	    $ftp->rename($file_only, $file_only.".".$d) or $newerr=1;
            push @ERRORS, "Can't rename file $!\n" if $newerr;
    	    myerr() if $newerr;
	}
	
	print "[UPLOADING] $folder$file_only \n";

	$ftp->put($folder.$file_only, $file_only) or $newerr=1;
	#$ftp->put($file_only, $file_only) or $newerr=1; #esto es para archivos sin subcarpetas
        push @ERRORS, "Can't put file $!\n" if $newerr;
	myerr() if $newerr;
	
#        push @ERRORS, "Can't get size file $!\n" if $newerr;
#        myerr() if $newerr;

	print "[UPLOAD OK] $folder$file_only \n";

#	$ftp->rename($file_only, $file_only.".".$d) or $newerr=1;
#        push @ERRORS, "Can't rename file $!\n" if $newerr;
#        myerr() if $newerr;
        

	$ftp->cwd($folder_back) or $newerr=1;
        push @ERRORS, "Can't cd  $!\n" if $newerr;
        myerr() if $newerr;
	
#        $ftp->quit;
#        exit(0);

    }

    $ftp->quit;


sub myerr {
  print "Error: \n";
  print @ERRORS;
  exit 0;
}
