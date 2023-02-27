package com.example.shinkansen2

class Constants {

    object Cart{
        const val MAX_CART_NUM:Int = 4
        const val STATUS_NUMBER_RETURN_RUNNING:Int = 5
        const val STATUS_NUMBER_FINISH_PROC:Int = 7
        const val STATUS_NUMBER_OBSTACLE_PROC:Int = 9
        const val STATUS_NUMBER_FALL_PREVENTION_STOP:Int = 10
    }

    object Seat{
        const val MAX_ROW_NUM:Int = 20
        const val MAX_COL_NUM:Int = 6
        const val MAX_GREEN_COL_NUM = 4
    }

    object StatusString{
        const val None_Sting:String = "接続設定"
        const val DisConnect_Sting:String = "通信エラー"
        const val Start_Sting:String = "検知開始"
        const val Stopping_Sting:String = "一時停止中"
        const val EndWait_Sting:String = "検知終了待ち"
        const val Detecting_Sting:String = "検知中..."
        const val End_Sting:String = "検知終了"
        const val Finish_Sting:String = "検知完了"
        const val ReturnStart_String:String = "後検知開始"
        const val LostItemDetect_String:String = "遺失物検知"
        const val Error_String:String = "動作異常"
        const val ReDetectPrepare_String:String = "再検知準備中"
        const val SettingError_String:String = "設定誤り"
    }

    object WorkingHoursSelection{
        val WORKING_HOURS_LIST:Array<String> = arrayOf("日勤","夜勤")
    }

    object TeamSelection{
        val TEAM_LIST:Array<String> = arrayOf("車内A班","車内B班","車内C班",
            "班長A班","班長B班","班長C班","倉庫1班","倉庫2班")
        val NIGHT_TEAM_LIST:Array<String> = arrayOf("車内A班","車内B班","車内C班","車内D班","車内E班",
            "班長A班","班長B班","班長C班","班長D班","班長E班","機動J","機動K")
        val TEAM_MAP:MutableMap<String,Array<String>> = mutableMapOf("日勤" to TEAM_LIST, "夜勤" to NIGHT_TEAM_LIST)
    }

    object HandleCarSelection{
        var HANDLE_CAR_LIST:Array<String> = arrayOf("1・2","3・4","5・6","7","8・9","10","11・12","13・14","15・16")
        var HANDLE_CAR_MAP: MutableMap<String, Array<String>> = mutableMapOf(
            "車内A班" to HANDLE_CAR_LIST,
            "車内B班" to HANDLE_CAR_LIST,
            "車内C班" to HANDLE_CAR_LIST,
            "車内D班" to HANDLE_CAR_LIST,
            "車内E班" to HANDLE_CAR_LIST,
            "班長A班" to arrayOf(""),
            "班長B班" to arrayOf(""),
            "班長C班" to arrayOf(""),
            "班長D班" to arrayOf(""),
            "班長E班" to arrayOf(""),
            "倉庫1班" to arrayOf(""),
            "倉庫2班" to arrayOf(""),
            "機動J" to arrayOf(""),
            "機動K" to arrayOf("")
        )
    }

    object TrackNoSelection{
        val TRACK_LIST:Array<String> = arrayOf("庫1番線","庫2番線","庫3番線","庫4番線",
            "庫5番線","庫6番線","庫7番線","庫8番線","庫9番線","庫10番線","庫11番線","庫12番線")
    }

    object TypeSelection{
        val TYPE_LIST:Array<String> = arrayOf("X","G","J","N","K","F")
    }

    object MaintenanceSelection{
        val MAINTENANCE_LIST:Array<String> = arrayOf("小A","中A")
    }

    object SeatInitialSetting{
        val SEAT_COUNT_LIST:List<Int> = listOf(13,20,17,20,18,20,15,17,16,17,13,20,18,20,16,15)
        val GREEN_TRAIN_NO:List<Int> = listOf(6,7,8)
        val WHEEL_CHAIR_TRAIN_NO:Int = 11
        val WHEEL_CHAIR_SEATS:List<Int> = listOf(12,13)
    }

    object InitSettingJsonFileName{
        val APP_SETTING:String = "AppSetting.json"
        val WORK_SPINNER_SETTING:String = "WorkSpinnerSetting.json"
        val TRAIN_SPINNER_SETTING:String = "TrainSpinnerSetting.json"
        val SETTING_ITEMS:String = "SettingItems.json"
    }

    object Update{
        const val LOCAL_FOLDER:String = "update"
        const val REMOTE_FOLDER:String = "/update/smartphone/"
        const val RESULT_SUCCESS:Int = 0
        const val RESULT_NON_FILE:Int = 1
        const val RESULT_INVALID_NAME:Int = 2
    }

    object Id{
        const val TRAIN_NO_ID:String = "TRAIN_NO"
        const val CART_IDX_ID:String = "CART_IDX"
        const val SHOW_CART_NO_ID:String = "SHOW_CART_NO"
        const val SELECT_DETECT_ID:String = "SELECT_TYPE"
        const val SHOW_ROW_NO_ID:String = "SHOW_ROW_NO"
        const val SELECT_ROW_ID:String = "SELECT_ROW"
        const val SELECT_COL_ID:String = "SELECT_COL"
        const val SHOW_COL_MARK_ID:String = "ShOW_COL_MARK"
        const val SELECT_COLOR_ID:String = "SELECT_COLOR"
        const val SELECT_SIDE_ID:String = "SELECT_SIDE"
        const val IS_REVERSE_ID:String = "IS_REVERSE"
    }

    object Detect{
        const val SEAT_DETECT:String = "seat"
        const val COVER_DETECT:String = "cover"
        const val SHELF_DETECT:String = "lostitem"
    }

    object FileNameOrderIndex{
        const val DETECT_DIRECTION = 1
        const val ERROR_TYPE = 2
        const val CART_DIRECTION = 3
        const val SEAT_ROW = 4
        const val SEAT_COL_OR_ITEM_COUNT = 5
        const val MAX_COUNT = 6
    }

    object NormalSeatFileNameOrderIndex{
        const val DEVICE_ID = 0
        const val ERROR_TYPE = 1
        const val DETECT_DIRECTION = 2
        const val SEAT_ROW = 3
        const val MAX_COUNT = 6
    }

    object DetectTimingNo{
        const val BEFORE_DETECT:Int = 0
        const val DETECTING:Int = 1
        const val RETURN_DETECTING:Int = 2
        const val RE_DETECT_PREPARE:Int = 3
    }

    object DetectError{
        const val SEAT_DIRT:String = "dirt"
        const val SEAT_WET:String = "wet"
        const val SEAT_THERMAL:String = "thermal"
        const val COVER_DIRT:String = "dirt"
        const val COVER_WRINKLE:String = "wrinkle"
        const val COVER_NONE:String = "noncover"
        const val COVER_OFF:String = "off"
    }

    object Side{
        const val SEA_SIDE:String = "海側"
        const val MOUNT_SIDE:String = "山側"
    }

    object Unzip{
        const val PASSWORD:String = "password"
        const val EXTENSION:String = "zip"
    }

    object Image{
        const val EXTENSION:String = "jpg"
    }

    object ReShot{
        const val FOLDER_NAME:String = "reshot"
    }

    object Communication{
        //const val IP_ADDRESS:String = "10.0.2.2" //デフォルト値 jsonファイルから設定するように変更
        //const val IP_ADDRESS:String = "127.0.0.1"
        const val IP_ADDRESS:String = "192.168.3.100"
        const val CLIENT_PORT:Int = 6500 // クライアント時
        const val SERVER_PORT:Int = 12345 // サーバー時 //デフォルト値 jsonファイルから設定するように変更
        const val SERVER_CONNECT_MAX = 4 // サーバー接続数上限
        const val CONNECTION_TRY_TIMES_MAX:Int = 20 //初回接続時のconnectリトライ回数上限
        const val FTP_CONNECTION_TRY_TIMES_MAX:Int = 3 //FTPのconnectリトライ回数上限
        const val INITIAL_CONNECT_INTERVAL:Long = 1000  //アプリ起動初回時の接続の試行周期
        const val CONNECTED_INTERVAL:Long = 1200  //接続確立後から状態確認送信までの間隔時間(msec)
        const val RETRY_INTERVAL:Long = 1000  //接続確立後から状態確認送信までの間隔時間(msec)
        const val SEND_RETRY_INTERVAL:Long = 1000 // sendリトライの間隔時間
        const val RECONNECT_WAIT_INTERVAL:Long = 100 // 再接続待機時のの間隔時間
        const val READ_RETRY_TIMES_MAX = 3  //readのリトライ回数上限
        const val SEND_RETRY_TIMES_MAX = 3  //sendのリトライ回数上限

        const val ACK_VALUE:Int = 0 //電文のACK時の値
        const val NACK_VALUE:Int = 1 //電文のACK時の値

        const val CART_RESPONSE_DISCONNECT = 1 //荷棚カートへの指令要求の応答(接続エラー)
        const val CART_RESPONSE_DUPLICATE = 2 //荷棚カートへの指令要求の応答(カート重複)

        const val CART_RESPONSE_DISCONNECT_MESSAGE = "カートと接続ができないのでエラー応答となりました。もう一度接続をご確認ください。"
        const val CART_RESPONSE_DUPLICATE_MESSAGE = "すでに検知中のカートへ送信されたためエラー応答となりました。もう一度設定をご確認ください。"

        const val SOCKET_TIMEOUT = 1500 //ソケット接続のタイムアウト時間
        const val RECV_RESPONSE_TIMEOUT = 1000 //メッセージ初回受信時(送信→受信までの時間)のタイムアウト時間
        const val RECV_SPAN_TIMEOUT = 100 //メッセージ初回受信時(送信→受信までの時間)のタイムアウト時間

        const val STREAM_READ_ERROR:Byte = -1 //inputStream.readの読み込みエラー時の値
        const val CLOSED_ERROR:Byte = -2 //socketのクローズエラー時の値
        const val READ_FORMAT_ERROR:Byte = -3 //読込電文フォーマットエラー時の値

        const val FTP_SERVER_ADDRESS: String = "192.168.3.100" //FTPサーバーアドレス
        // TODO PCによっては21番ポートが使えない
        const val FTP_SERVER_PORT: Int = 21 //FTPサーバーポート
        const val FTP_TIMEOUT: Int = 1200 //FTP各種タイムアウトミリ秒
        const val CONNECT_WAIT_INTERVAL: Int = 400 //FTP各種タイムアウトミリ秒
        const val USER_ID: String = "user"         //ログインユーザID
        const val PASSWORD: String = "password"      //ログインパスワード

        const val SEQUENCE_NUMBER_MAX = 65535 //シーケンス番号の最大値

    }

    object MessageFormat{

        // 共通バッファイサイズ
        const val RESPONSE_SIZE = 10 // 応答電文のバッファサイズ
        const val MAX_RECEIVE_BUFFER_SIZE = 1024 // 受信最大バッファサイズ

        // 送信ID スマホクライアント（スマホ・サーバ間　固有）
        const val TABLE_DATA_NOTIFICATION_ID = 0x3001               // テーブルデータ通知電文
        const val DETECTION_TREATMENT_REQUEST_ID = 0x3201           // 検知処置送信電文
        const val TREATMENT_FINISH_ID = 0x3301                      // 処置完了電文
        const val WORK_FINISH_NOTIFICATION_ID = 0x3601              // 作業終了通知電文
        const val DETECT_INFORMATION_REQUEST_ID = 0x3701            // 検知情報要求電文
        // 送信ID スマホクライアント（スマホ・サーバ・カート間　共有）
        const val HASH_VALUE_NOTIFICATION_ID = 0x0001               // ハッシュ値通知電文
        const val RUN_START_REQUEST_ID = 0x0501                     // 走行開始要求電文
        const val CART_SETTING_REQUEST_ID = 0x0601                  // 検知車両設定通知要求電文
        const val RUN_STOP_REQUEST_ID = 0x0701                      // 走行停止要求電文
        const val NORMAL_SEAT_IMAGE_REQUEST_ID = 0x0801             // 正常座席画像要求電文

        // 受信ID スマホクライアント（スマホ・サーバ間　固有）
        const val TABLE_DATA_NOTIFICATION_RESPONSE_ID = 0x3002      // テーブルデータ通知応答電文
        const val DETECTION_TREATMENT_RESPONSE_ID = 0x3202          // 検知処置応答電文
        const val TREATMENT_FINISH_RESPONSE_ID = 0x3302             // 処置完了応答電文
        const val WORK_FINISH_NOTIFICATION_RESPONSE_ID = 0x3602     // 作業終了通知応答電文
        const val DETECT_INFORMATION_RESPONSE_ID = 0x3702           // 検知情報要求電文
        // 受信ID スマホクライアント（スマホ・サーバ・カート間　共有）
        const val HASH_VALUE_NOTIFICATION_RESPONSE_ID = 0x0002      // ハッシュ値通知応答電文
        const val RUN_START_RESPONSE_ID = 0x0502                    // 走行開始応答電文
        const val CART_SETTING_RESPONSE_ID = 0x0602                 // 検知車両設定通知応答電文
        const val RUN_STOP_RESPONSE_ID = 0x0702                     // 走行停止応答電文
        const val NORMAL_SEAT_IMAGE_RESPONSE_ID = 0x0802             // 正常座席画像応答電文


        // 受信ID スマホサーバー（スマホ・サーバ間　固有）
        const val IMG_FILENAME_NOTIFICATION_ID = 0x3101             // 画像ファイル名通知電文
        const val TREATMENT_RESULT_ID = 0x3401                      // 処置送信結果電文
        const val CART_CONNECTION_ERROR_NOTIFICATION_ID = 0x3501    // カート接続エラー通知電文
        // 受信ID スマホサーバー（スマホ・サーバ・カート間　共有）
        const val CART_POSITION_NOTIFICATION_ID = 0x0101            // カート位置通知電文
        const val ERROR_NOTIFICATION_ID = 0x0201                    // エラー通知電文
        const val SETTING_ERROR_NOTIFICATION_ID = 0x0301            // 設定異常通知電文
        const val CART_SITUATION_NOTIFICATION_ID = 0x0401           // カート状態通知電文

        // 送信ID スマホサーバー（スマホ・サーバ間　固有）
        const val IMG_FILENAME_NOTIFICATION_RESPONSE_ID = 0x3102            // 画像ファイル名通知応答電文
        const val TREATMENT_RESULT_RESPONSE_ID = 0x3402                     // 処置送信結果応答電文
        const val CART_CONNECTION_ERROR_NOTIFICATION_RESPONSE_ID = 0x3502   // カート接続エラー通知応答電文
        // 送信ID スマホサーバー（スマホ・サーバ・カート間　共有）
        const val CART_POSITION_NOTIFICATION_RESPONSE_ID = 0x0102           // カート位置通知応答電文
        const val ERROR_NOTIFICATION_RESPONSE_ID = 0x0202                   // エラー通知応答電文
        const val SETTING_ERROR_NOTIFICATION_RESPONSE_ID = 0x0302           // 設定異常通知応答電文
        const val CART_SITUATION_NOTIFICATION_RESPONSE_ID = 0x0402          // カート状態通知応答電文

    }

    object MessageLength{

        //共通(ヘッダー関連)
        const val MESSAGE_ID_SIZE = 2
        const val SEQUENCE_SIZE = 2
        const val LENGTH_SIZE = 2
        const val HEADER_SIZE = MESSAGE_ID_SIZE + SEQUENCE_SIZE + LENGTH_SIZE
        const val CHECK_SUM_SIZE = 1
        const val APART_FROM_BODY_SIZE = HEADER_SIZE + CHECK_SUM_SIZE // データ部を除いたバッファサイズ

        //共通(応答関連)
        const val ACK_NACK_SIZE = 1

        //共通(スマホID、カートID)
        const val APP_ID_SIZE = 2
        const val CART_ID_SIZE = 2

        //const val TABLE_DATA_NOTIFICATION_ID = 0x0001 // テーブルデータ通知電文
        const val PORT_NO_SIZE = 2

        //const val HASH_VALUE_NOTIFICATION_ID = 0x0601 // ハッシュ値通知電文(可変)
        const val HASH_SIZE = 16

        const val HASH_CALC_BUFFER_SIZE = 1024
    }

    object Vibration{
        const val HighInterval: Long = 500
        const val LowInterval:Long = 1000
        const val HighAmp:Int = 50
        const val LowAmp:Int = 0
        const val RepeatCount:Int = 0
    }

    object Maintenance{
        const val PASSWORD: String = "SMT"
        const val TYPE_STRING_SEND:String = "send"
        const val TYPE_STRING_REQUEST:String = "request"
        const val TYPE_STRING_RECV:String = "recv"
        const val TYPE_STRING_STATUS:String = "status"
        const val TYPE_STRING_ONE_SHOT:String = "oneshot"
        const val TYPE_STRING_LOG:String = "log"
        const val CART_LOG_FOLDER: String = "Log"
        const val CART_STATUS_FOLDER: String = "Status"
    }

    object RemoteFilePathString{
        const val IMAGE_FOLDER_STRING = "log/"
        const val UPDATE_FOLDER_STRING = "update/"
        const val MAINTENANCE_FOLDER_STRING = "maintenance/"
        const val SETTING_FOLDER_STRING = "setting/"
    }
    
    object Request{
        const val ONE_WAY_DIST_DEFAULT:Int = 200
        const val RADIO_TEXT_LEFT:String = "左"
        const val RADIO_TEXT_RIGHT:String = "右"
        const val RADIO_TEXT_OPPOSITE:String = "反対側"
        const val RADIO_VALUE_LEFT:String = "l"
        const val RADIO_VALUE_RIGHT:String = "r"
        const val RADIO_VALUE_OPPOSITE:String = "o"
    }

    object OneShot{
        const val SEAT:String = "座席"
        const val LOST_ITEM:String = "遺失物"
        const val CAPTURE_IMAGE_KEY:String = "img"
        const val SEAT_DETECTED_KEY:String = "detected"
        const val SEAT_STATE_KEY:String = "seat_state"
        const val SEAT_NUM_KEY:String = "pos"
        const val SEAT_STATUS_KEY:String = "seat_state"
        const val SEAT_DIRT_KEY:String = "is_dirt"
        const val SEAT_WET_KEY:String = "is_wet"
        const val SEAT_PATH_KEY:String = "seat_path"
        const val THERMAL_PATH_KEY:String = "thermal_path"
        const val COVER_STATUS_KEY:String = "cover_state"
        const val COVER_NO_COVER_KEY:String = "no_cover"
        const val COVER_WRINKLE_KEY:String = "is_wrinkle"
        const val COVER_DIRT_KEY:String = "is_cover_dirt"
        const val COVER_OFF_KEY:String = "is_off"
        const val COVER_PATH_KEY:String = "cover_path"
        const val LOST_ITEM_DETECTED_KEY:String = "detected_result"
        const val LOST_ITEM_IMAGE_KEY:String = "result_img"
    }

    object ErrorCode{
        // 異常なし
        const val NoError = -1

        //アップデート
        const val Update = 0

        // 距離センサ関連 1～10
        const val Dist_RangeLeft = 1                   // 左向き距離センサ
        const val Dist_RangeRight = 2                  // 右向き距離センサ
        const val Dist_ObstToFLeft = 3                 // 左向きToF衝突防止センサ
        const val Dist_ObstToFRight = 4                // 右向きToF衝突防止センサ
        const val Dist_ObstWaveLeft = 5                // 左向き超音波衝突防止センサ  // TODO 使用用途は？
        const val Dist_ObstWaveRight = 6               // 右向き超音波衝突防止センサ  // TODO 使用用途は？
        const val Dist_DropLeft = 7                    // 左側落下防止センサ
        const val Dist_DropRight = 8                   // 右側落下防止センサ

        // カメラ関連 11～20
        const val Camera_OverheadBinLeft = 11          // 左向き荷棚カメラ
        const val Camera_OverheadBinRight = 12         // 右向き荷棚カメラ
        const val Camera_SeatLeft = 13                 // 左向き座席カメラ
        const val Camera_SeatRight = 14                // 右向き座席カメラ
        const val Camera_SeatOpposite = 15             // 反対側座席カメラ
        const val Camera_ThermalLeft = 16              // 左向き座席カメラ
        const val Camera_ThermalRight = 17             // 右向き座席カメラ
        const val Camera_ThermalOpposite = 18          // 反対側座席カメラ

        // LED 21～30
        const val LED_DarkSeat = 21                    // 座席向け暗所時照明
        const val LED_DarkOverheadBin = 22             // 荷棚向け暗所時照明
        const val LED_DarkOpposite = 23                // 反対側の座席向け暗所時照明
        const val LED_Battery = 24                     // バッテリ残量表示用LED照明
        const val LED_Status = 25                      // ステータス表示用LED
        const val LED_7Seg = 26                        // 7セグ
        const val LED_SW1 = 27                         // SW1LED
        const val LED_SW2 = 28                         // SW2LED

        // バッテリ関連 31～40
        const val Battery_AnomalyTemp = 31             // 温度異常
        const val Battery_Dead = 32                    // バッテリー切れ
        const val Battery_VoltageError = 33            // バッテリー電圧異常
        const val Battery_DrainError = 34              // バッテリー消耗
        const val Battery_AnomalyComms = 35            // バッテリー通信異常

        // 温度異常 41～50
        const val Temp_SensorTemp = 40                 // 温度センサ取得値が異常
        const val Temp_MainBoardCPU = 41               // Armadillo CPU温度
        const val Temp_AI1BoardCPU = 42                // AI基板1のCPU温度異常
        const val Temp_AI2BoardCPU = 43                // AI基板2のCPU温度異常

        // その他 51～70
        const val Other_Switch2 = 51                   // SW2制御異常
        const val Other_Buzzer = 52                    // ブザー制御異常
        const val Other_MotorLeft = 53                 // 左側モーター制御異常
        const val Other_MotorRight = 54                // 右側モーター制御異常
        const val Other_TempSensor = 55                // 温度センサー制御事情
        const val Other_USBNotConnect = 56             // USBマウント失敗
        const val Other_LowMemory = 57                 // USB容量不足

        // ソフト関連エラー 71～80
        const val FW_FailedDetecting = 71              // 検出失敗
        const val FW_ServerComms = 72                  // 通信エラー
        const val FW_CPUComms = 73                     // CPU間通信異常
        const val FW_ReadData = 74                     // データ読み込み異常
        const val FW_WriteData = 75                    // データ書き込み異常  // TODO 使用用途は？
        const val FW_Parameter = 76                    // パラメータ異常
        const val FW_FilePath = 77                     // ファイルパス異常  // TODO 使用用途は？
        const val FW_SoftOther = 78                    // その他FWエラー

        // 警告 80～99
        const val Warning_MaintenanceTiming = 80       // メンテナンス時期通知  // TODO 使用用途は？
        const val Warning_BatteryExchange = 81         // バッテリ交換時期通知
        const val Warning_USBCapacityDecrease = 82     // USB容量減少     // TODO 使用用途は？
        const val Warning_UnexpectedReceive = 83       // 意図しない受信  // TODO 使用用途は？
        const val Warning_ReplaceTimingMotor = 84      // モーターの交換時期
        const val Warning_ReplaceTimingTire = 85       // タイヤの交換時期
        const val Warning_ReplaceTimingResin = 86      // 樹脂材の交換時期
        const val Warning_ReplaceTimingDarkLED = 87    // 暗所用LEDの交換時期
        const val Warning_ReplaceTimingBattLED = 88    // バッテリ用LEDの交換時期
        const val Warning_ReplaceTimingStatusLED = 89  // 状態表示LEDの交換時期
        const val Warning_ReplaceTiming7Seg = 90       // 7セグの交換時期
        const val Warning_ReplaceTimingSW1 = 91        // SW1 LEDの交換時期
        const val Warning_ReplaceTimingSW2 = 92        // SW2 LEDの交換時期

        //緊急停止(落下)
        const val FallPreventionStop = 101

    }

    object WarningCode{
        //メンテナンス時期通知
        const val MaintenanceTimeNotify = 36
        //バッテリー交換
        const val BatteryExchange = 37
        //USBメモリ容量低下
        const val USBCapacityDecrease = 38
        //意図しない箇所に電文が送られた場合の通知
        const val UnexpectedReceiveNotify = 39
    }

    object BackUpDataKey{
        const val SERIAL_NO_DATA_KEY:String = "serialNumberData"
        const val CART_DATA_KEY:String = "cartListData"
        const val SETTING_DATA_KEY:String = "settingListData"
        const val OBSTACLE_PATH_KEY:String = "obstacleFilePathsListData"
        const val SEAT_DETECT_DATA_KEY:String = "seatDetectListData"
        const val SHELF_DETECT_DATA_KEY:String = "shelfDetectListData"
    }

    object StatusKey{
        const val VERSION:String = "version"
        const val STATISTICS:String = "statistics"
        const val BATTERY:String = "battery"
        const val DISTANCE_SENSOR:String = "distance_sensor"
        const val FALL_SENSOR:String = "fall_sensor"
        const val OBSTACLE_SENSOR:String = "obstacle_sensor"
        const val LIGHTNESS:String = "lightness"
        const val TEMPERATURE:String = "temperature"
    }

    object LogKey{
        const val BOARD_MAIN:String = "main"
        const val BOARD_AI1:String = "AI1"
        const val BOARD_AI2:String = "AI2"
        const val FILE_SYSTEM:String = "syslog"
        const val FILE_KERNEL:String = "kernel"
        const val FILE_PROGRAM:String = "program"
    }

    object RequestKey{
        const val STATUS_SEND:String = "status_send"
        object StatusSend{
            const val IS_ENABLE:String = "is_enable"
        }

        const val PARAMETER_SEND:String = "parameter_send"
        object ParameterSend{
            const val KEEP_FOREVER:String = "keep_forever"
            const val DEVICE:String = "device"
            const val POSITION:String = "position"
            const val WET:String = "wet"
            const val DIRT:String = "dirt"
            const val COVER:String = "cover"
            const val LOSTITEMS:String = "lostitems"
        }

        const val RESET_STATISTICS:String = "reset_statistics"
        object ResetStatistics{
            const val POWER_ON_TIME:String = "power_on_time"
            const val MILEAGE:String = "mileage"
            const val MOTOR:String = "motor"
            const val TIRE:String = "tire"
            const val RESIN_MATERIAL:String = "resin_material"
            const val LED_DARK:String = "led_dark"
            const val LED_BATTERY:String = "led_battery"
            const val LED_STATUS:String = "led_status"
            const val LED_7SEG:String = "led_7seg"
            const val LED_SW1:String = "led_sw1"
            const val LED_SW2:String = "led_sw2"
        }
        const val FACTORY_STATE_CHECK:String = "factory_state_check"
        object FactoryStateCheck{
            const val IS_ENABLE:String = "is_enable"
        }
        const val ENDLESS_DRIVING:String = "endless_driving"
        object EndlessDriving{
            const val IS_ENABLE:String = "is_enable"
            const val FIRST_DRIVING_DIRECTION:String = "first_driving_direction"
            const val IS_DRIVING_TO_WALL:String = "is_driving_to_wall"
            const val ONE_WAY_DIST:String = "one_way_dist"
            const val IS_CAPTURE:String = "is_capture"
            const val IS_DETECT:String = "is_detect"
            const val DARK_LED_ON:String = "dark_led_on"
        }
        const val ONE_TIME_SEAT_DETECTION:String = "one_time_seat_detection"
        object OneTimeSeatDetection{
            const val IS_ENABLE:String = "is_enable"
            const val DARK_LED_ON:String = "dark_led_on"
            const val CAMERA:String = "camera"
        }
        const val ONE_TIME_LOST_ITEM_DETECTION:String = "one_time_lost_item_detection"
        object OneTimeLostItemDetection{
            const val IS_ENABLE:String = "is_enable"
            const val DARK_LED_ON:String = "dark_led_on"
            const val CAMERA:String = "camera"
        }
        const val DISABLE_DETECTION:String = "disable_detection"
        object DisableDetection{
            const val COVER_DIRT:String = "cover_dirt"
            const val COVER_WRINKLE:String = "cover_wrinkle"
            const val COVER_OFF:String = "cover_off"
            const val COVER_NOTHING:String = "cover_nothing"
            const val SEAT_DIRT:String = "seat_dirt"
            const val SEAT_WET:String = "seat_wet"
            //const val SEAT_RUBBING:String = "seat_rubbing"
            const val LOST_ITEM:String = "lost_item"
        }
        const val LOG:String = "log"
        object Log{
            const val MAIN:String = "main"
            object Main{
                const val FW:String = "fw"
                const val SYSLOG:String = "syslog"
                const val KERNEL:String = "kernel"
            }
            const val AI1:String = "AI1"
            object Ai1{
                const val FW:String = "fw"
                const val SYSLOG:String = "syslog"
                const val KERNEL:String = "kernel"
            }
            const val AI2:String = "AI2"
            object Ai2{
                const val FW:String = "fw"
                const val SYSLOG:String = "syslog"
                const val KERNEL:String = "kernel"
            }
        }
    }

}