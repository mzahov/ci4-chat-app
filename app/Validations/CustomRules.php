<?php 
namespace App\Validations;

class CustomRules {

     public function alpha_only($value, ?string &$error = null): bool
     {
          if (mb_ereg_match('^[[:alpha:]]+$', $value ?? '', 'u')) {
               return true;
          }

          $error = lang('validation.alpha_only');
          return false;
     }
 
     public function alpha_num($value, ?string &$error = null): bool
     {
          if (mb_ereg_match('^[[:alnum:]]+$', $value ?? '', 'u')) return true;

          $error = lang('validation.alpha_num');
          return false;
     }
     
     public function alpha_num_space($value, ?string &$error = null): bool
     {
          return (bool) mb_ereg_match('^[[:alnum:]\sА-Яа-яЁё]+$',$value ?? '', 'u');
     }

     public function alpha_space_utf($value, ?string &$error = null): bool
     {
          if (mb_ereg_match('^[[:alpha:]\sА-Яа-яЁё]+$',$value ?? '', 'u')) return true;

          $error = lang('validation.alpha_space_utf');
          return false;
     }
}