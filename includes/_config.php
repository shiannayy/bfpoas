<?php 
 class config {
    //public const DB_HOST= "localhost";
    //public const DB_USER= "u444252215_fsed";
    //public const DB_PASS= "#4syzjWK";
    //public const DB_NAME= "u444252215_fsedoas";
  
  public const DB_HOST= "localhost";
  public const DB_USER= "root";
  public const DB_PASS= "";
  public const DB_NAME= "fsed";

    public const ADMIN_EMAIL= "ashianna395@gmail.com";

     
    public const APP_SECRET='my_super_secret_encryption_key';
    public const REGION= "R05";
    public const P_REGION= "REGION V";
    public const P_DIST_OFFICE="3rd District of Albay";
    public const P_STATION="Oas Fire Station";
    public const P_STATION_ADDRESS="Brgy. Iraya Norte, Oas, Albay";
    public const P_STATION_CONTACT="(+63) 917 701 7938 / oasfirestation@yahoo.com";
    public const CURR_MUNICIPALITY="OAS";
    public const CURR_PROVINCE="ALBAY";
    public const CURR_POSTAL_CODE="4504";
     
    public const MAX_RESCHEDULE_COUNT=2;
    public const BFP_REV="BFP-QSF-FSED-005 Rev. 03 (03.03.20)";
    const BASE_FS_CODE = "050108";


   public const DILG_LOGO = "../assets/img/dilg.jpg";
    public const BFP_LOGO = "../assets/img/bfp-logo.jpg";
}

define("FS_CODE", "R". config::BASE_FS_CODE . "-" . date('y'));
// Result: "R050108-25"