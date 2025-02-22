<?php
/*
Plugin Name: Kboard Extend
Plugin URI: https://github.com/XisoDev/kboard-extend
Description: KBoard 기능 확장 플러그인
Version: 1.0
Author: xiso
Author URI: https://amuz.co.kr
License: A "Slug" license name e.g. GPL2
*/

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/inc/KBoardReport.php';
require_once __DIR__ . '/inc/KBoardReportSettings.php';

// 플러그인 인스턴스 시작
KBoardReport::getInstance();