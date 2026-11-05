<?php

namespace LarExtend\Models;

use Illuminate\Notifications\DatabaseNotification;

/**
 * 通知管理模型
 * 复用 Laravel 数据库通知表及数据转换，单独注册后台管理策略。
 */
class Notification extends DatabaseNotification {}
