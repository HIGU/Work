package com.example.shinkansen2

import android.annotation.SuppressLint
import android.content.Context
import android.graphics.Bitmap
import android.graphics.BitmapFactory
import android.os.Bundle
import android.util.Log
import androidx.fragment.app.Fragment
import android.view.LayoutInflater
import android.view.View
import android.view.ViewGroup
import android.widget.Button
import android.widget.ImageView
import android.widget.TextView
import androidx.fragment.app.activityViewModels
import androidx.lifecycle.Observer
import androidx.lifecycle.ProcessLifecycleOwner
import com.example.shinkansen2.Data.CartData
import com.example.shinkansen2.Data.CartDataViewModel
import com.example.shinkansen2.Data.CartStatus
import com.example.shinkansen2.Utils.LogExport


data class ErrorTableItem(
    val code:Int,
    val title:String,
    val message:String
);

class ErrorFragment : Fragment() {

    private val TAG = this.javaClass.simpleName

    private var cartNo:Int = 0
    private lateinit var listener: CallbackListener
    private val viewModel: CartDataViewModel by activityViewModels()

    interface CallbackListener {
        fun confirmationClickFromFragment(cartIdx:Int)
        fun backClickFromFragment()
    }



    object ErrorTextTable{
//region エラーコードタイトル
        val table:List<ErrorTableItem> = listOf(
            // 距離センサ関連
            ErrorTableItem(Constants.ErrorCode.NoError,             "異常なし",            "異常はありませんでした。"),
            ErrorTableItem(Constants.ErrorCode.Update,              "アップデート中",       "アップデートエラー"),
            ErrorTableItem(Constants.ErrorCode.Dist_RangeLeft,      "距離センサ(左)故障",   "モーター(左)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Dist_RangeRight,     "距離センサ(右)故障",   "モーター(右)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Dist_ObstToFLeft,    "ToFセンサ(左)故障",    "ToFセンサ(左)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Dist_ObstToFRight,   "ToFセンサ(右)故障",    "ToFセンサ(右)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Dist_ObstWaveLeft,   "超音波センサ(左)故障", "超音波センサ(左)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Dist_ObstWaveRight,  "超音波センサ(右)故障", "超音波センサ(右)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Dist_DropLeft,       "落下センサ(左)故障",   "落下センサ(左)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Dist_DropRight,      "落下センサ(右)故障",   "落下センサ(右)が故障しています。"),

            // カメラ関連
            ErrorTableItem(Constants.ErrorCode.Camera_OverheadBinLeft,  "荷棚カメラ(左)故障",       "荷棚カメラ(左)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Camera_OverheadBinRight, "荷棚カメラ(右)故障",       "荷棚カメラ(右)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Camera_SeatLeft,         "座席カメラ(左)故障",       "座席カメラ(左)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Camera_SeatRight,        "座席カメラ(右)故障",       "座席カメラ(右)が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Camera_SeatOpposite,     "座席カメラ(反対)故障",     ""),
            ErrorTableItem(Constants.ErrorCode.Camera_ThermalLeft,      "サーマルカメラ故障",       "サーマルカメラが故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Camera_ThermalRight,     "サーマルカメラ(右)故障",   ""),
            ErrorTableItem(Constants.ErrorCode.Camera_ThermalOpposite,  "サーマルカメラ(反対)故障", "サーマルカメラOppが故障しています。"),

            //LED
            ErrorTableItem(Constants.ErrorCode.LED_DarkSeat,        "座席LED故障",          ""),
            ErrorTableItem(Constants.ErrorCode.LED_DarkOverheadBin, "荷棚LED故障",          ""),
            ErrorTableItem(Constants.ErrorCode.LED_DarkOpposite,    "座席LED故障",          ""),
            ErrorTableItem(Constants.ErrorCode.LED_Battery,         "バッテリーLED故障",    "バッテリーLEDが故障しています。"),
            ErrorTableItem(Constants.ErrorCode.LED_Status,          "ステータスLED故障",    ""),
            ErrorTableItem(Constants.ErrorCode.LED_7Seg,            "7セグLED故障",         ""),
            ErrorTableItem(Constants.ErrorCode.LED_SW1,             "スイッチ1LED故障",     "スイッチ1LEDが故障しています。"),
            ErrorTableItem(Constants.ErrorCode.LED_SW2,             "スイッチ2LED故障",     "スイッチ2LEDが故障しています。"),

            //バッテリ関連
            ErrorTableItem(Constants.ErrorCode.Battery_AnomalyTemp,     "バッテリー温度異常",   "バッテリー温度異常が発生しています。"),
            ErrorTableItem(Constants.ErrorCode.Battery_Dead,            "バッテリー切れ",       "バッテリー切れが発生しています。"),
            ErrorTableItem(Constants.ErrorCode.Battery_VoltageError,    "",                    ""),
            ErrorTableItem(Constants.ErrorCode.Battery_DrainError,      "",                    ""),
            ErrorTableItem(Constants.ErrorCode.Battery_AnomalyComms,    "バッテリー通信異常",   "バッテリー通信異常が発生しています。"),

            //温度異常
            ErrorTableItem(Constants.ErrorCode.Temp_SensorTemp,     "温度異常",                "温度センサーが故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Temp_MainBoardCPU,   "メインボードCPU温度異常",  "メインボードCPU温度異常が発生しています。"),
            ErrorTableItem(Constants.ErrorCode.Temp_AI1BoardCPU,    "",                       ""),
            ErrorTableItem(Constants.ErrorCode.Temp_AI2BoardCPU,    "",                       ""),

            //その他
            ErrorTableItem(Constants.ErrorCode.Other_Switch2,       "スイッチ2故障",    "スイッチ2が故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Other_Buzzer,        "ブザー背面故障",   "ブザーが故障しています。"),
            ErrorTableItem(Constants.ErrorCode.Other_MotorLeft,     "モーター(左)故障", ""),
            ErrorTableItem(Constants.ErrorCode.Other_MotorRight,    "モーター(右)故障", ""),
            ErrorTableItem(Constants.ErrorCode.Other_TempSensor,    "温度センサー故障", ""),
            ErrorTableItem(Constants.ErrorCode.Other_USBNotConnect, "USBメモリ未接続",  "USBメモリ未接続状態です。"),
            ErrorTableItem(Constants.ErrorCode.Other_LowMemory,     "メモリ不足",       "メモリ不足が発生しています。"),

            //ソフト関連エラー
            ErrorTableItem(Constants.ErrorCode.FW_FailedDetecting,  "検知処理異常",            "検知処理異常が発生しています。"),
            ErrorTableItem(Constants.ErrorCode.FW_ServerComms,      "状態管理アプリ通信異常",   "状態管理アプリ通信異常が発生しています。"),
            ErrorTableItem(Constants.ErrorCode.FW_CPUComms,         "CPU間通信異常",           "CPU間通信異常が発生しています。"),
            ErrorTableItem(Constants.ErrorCode.FW_ReadData,         "データ読み取りエラー",     "データ読み取りエラーが発生しています。"),
            ErrorTableItem(Constants.ErrorCode.FW_WriteData,        "データ書き込みエラー",     "データ書き込みエラーが発生しています。"),
            ErrorTableItem(Constants.ErrorCode.FW_Parameter,        "パラメータ異常",           "パラメータ異常が発生しています。"),
            ErrorTableItem(Constants.ErrorCode.FW_FilePath,         "ファイルパス異常",         "ファイルパス異常が発生しています。"),
            ErrorTableItem(Constants.ErrorCode.FW_SoftOther,        "ソフト制御異常(Other)",    "ソフト制御異常(その他異常)が発生しています。"),

            //警告
            ErrorTableItem(Constants.ErrorCode.Warning_MaintenanceTiming,       "メンテナンス時期通知", "メンテナンス時期の通知をします。"),
            ErrorTableItem(Constants.ErrorCode.Warning_BatteryExchange,         "バッテリー交換",       "バッテリー交換をしてください。"),
            ErrorTableItem(Constants.ErrorCode.Warning_USBCapacityDecrease,     "USBメモリ容量低下",    "USBメモリの容量が低下しています。"),
            ErrorTableItem(Constants.ErrorCode.Warning_UnexpectedReceive,       "意図せぬ電文",         "意図しない箇所に電文が送られました。"),
            ErrorTableItem(Constants.ErrorCode.Warning_ReplaceTimingMotor,      "",""),
            ErrorTableItem(Constants.ErrorCode.Warning_ReplaceTimingTire,       "",""),
            ErrorTableItem(Constants.ErrorCode.Warning_ReplaceTimingResin,      "",""),
            ErrorTableItem(Constants.ErrorCode.Warning_ReplaceTimingDarkLED,    "",""),
            ErrorTableItem(Constants.ErrorCode.Warning_ReplaceTimingBattLED,    "",""),
            ErrorTableItem(Constants.ErrorCode.Warning_ReplaceTimingStatusLED,  "",""),
            ErrorTableItem(Constants.ErrorCode.Warning_ReplaceTiming7Seg,       "",""),
            ErrorTableItem(Constants.ErrorCode.Warning_ReplaceTimingSW1,        "",""),
            ErrorTableItem(Constants.ErrorCode.Warning_ReplaceTimingSW2,        "",""),

            //緊急停止(落下)
            ErrorTableItem(Constants.ErrorCode.FallPreventionStop,  "落下防止用緊急停止",   "落下の危険性があり緊急停止しました。\n カートを設置しなおしてください。"),
        )

        const val distanceSensorLeft_title = "距離センサ(左)故障"
        const val distanceSensorRight_title = "距離センサ(右)故障"
        const val tofObstacleSensorLeft_title = "ToFセンサ(左)故障"
        const val tofObstacleSensorRight_title = "ToFセンサ(右)故障"
        const val waveObstacleSensorLeft_title = "超音波センサ(左)故障"
        const val waveObstacleSensorRight_title = "超音波センサ(右)故障"
        const val dropSensorsLeft_title = "落下センサ(左)故障"
        const val dropSensorsRight_title = "落下センサ(右)故障"

        const val shelfCameraLeft_title = "荷棚カメラ(左)故障"
        const val shelfCameraRight_title = "荷棚カメラ(右)故障"
        const val seatCameraLeft_title = "座席カメラ(左)故障"
        const val seatCameraRight_title = "座席カメラ(右)故障"
        const val seatCameraOpposite_title = "座席カメラ(反対)故障"
        const val seatThermalCameraLeft_title = "サーマルカメラ故障"
        const val seatThermalCameraRight_title = "サーマルカメラ(右)故障"
        const val seatThermalCameraOpposite_title = "サーマルカメラ(反対)故障"

        const val seatLED_title = "座席LED故障"
        const val shelfLED_title = "荷棚LED故障"
        const val seatOppositeLED_title = "座席LED故障"
        const val batteryLED_title = "バッテリーLED故障"
        const val statusLED_title = "ステータスLED故障"
        const val sw1LED_title = "スイッチ1LED故障"
        const val sw2LED_title = "スイッチ2LED故障"
        const val sevenSegLED_title = "7セグLED故障"

        const val anomalyTemp_title = "温度異常"
        const val batteryDead_title = "バッテリー切れ"
        const val buzzer_title = "ブザー故障"

        const val anomalyBatteryTemp_title = "バッテリー温度異常"
        const val anomalyBatteryComms_title = "バッテリー通信異常"

        const val buzzerBack_title = "ブザー背面故障"
        const val conditionNotifyLED_title = "状態通知LED故障"
        const val switch2_title = "スイッチ2故障"
        const val tempSensor_title = "温度センサー故障"
        const val motorLeft_title = "モーター(左)故障"
        const val motorRight_title = "モーター(右)故障"
        const val USBNotConnect_title = "USBメモリ未接続"
        const val lowMemory_title = "メモリ不足"
        const val abnormalMainBoardCPUTemp_title = "メインボードCPU温度異常"

        const val detectProcess_title = "検知処理異常"
        const val conditionManagerAppComms_title = "状態管理アプリ通信異常"
        const val CPUComms_title = "CPU間通信異常"
        const val readData_title = "データ読み取りエラー"
        const val writeData_title = "データ書き込みエラー"
        const val parameter_title = "パラメータ異常"
        const val filePath_title = "ファイルパス異常"
        const val softOther_title = "ソフト制御異常(Other)"
        const val fallPreventionStop_title = "落下防止用緊急停止"

//endregion

//region エラーコード本文
        const val noError_main = "異常はありませんでした。"
        const val motorLeft_main = "モーター(左)が故障しています。"
        const val motorRight_main = "モーター(右)が故障しています。"
        const val distanceSensorLeft_main = "距離センサ(左)が故障しています。"
        const val distanceSensorRight_main = "距離センサ(右)が故障しています。"
        const val tofObstacleSensorLeft_main = "ToFセンサ(左)が故障しています。"
        const val tofObstacleSensorRight_main = "ToFセンサ(右)が故障しています。"
        const val waveObstacleSensorLeft_main = "超音波センサ(左)が故障しています。"
        const val waveObstacleSensorRight_main = "超音波センサ(右)が故障しています。"
        const val dropSensorsLeft_main = "落下センサ(左)が故障しています。"
        const val dropSensorsRight_main = "落下センサ(右)が故障しています。"

        const val shelfCameraLeft_main = "荷棚カメラ(左)が故障しています。"
        const val shelfCameraRight_main = "荷棚カメラ(右)が故障しています。"
        const val seatCameraLeft_main = "座席カメラ(左)が故障しています。"
        const val seatCameraRight_main = "座席カメラ(右)が故障しています。"
        const val thermalCamera_main = "サーマルカメラが故障しています。"
        const val thermalCameraOpp_main = "サーマルカメラOppが故障しています。"

        const val batteryLED_main = "バッテリーLEDが故障しています。"
        const val sw1LED_main = "スイッチ1LEDが故障しています。"
        const val sw2LED_main = "スイッチ2LEDが故障しています。"

        const val anomalyBatteryTemp_main = "バッテリー温度異常が発生しています。"
        const val batteryDead_main = "バッテリー切れが発生しています。"
        const val anomalyBatteryComms_main = "バッテリー通信異常が発生しています。"

        const val tempSensor_main = "温度センサーが故障しています。"
        const val abnormalMainBoardCPUTemp_main = "メインボードCPU温度異常が発生しています。"

        const val switch2_main = "スイッチ2が故障しています。"
        const val buzzer_main = "ブザーが故障しています。"
        const val USBNotConnect_main = "USBメモリ未接続状態です。"
        const val lowMemory_main = "メモリ不足が発生しています。"

        const val detectProcess_main = "検知処理異常が発生しています。"

        const val conditionManagerAppComms_main = "状態管理アプリ通信異常が発生しています。"
        const val CPUComms_main = "CPU間通信異常が発生しています。"
        const val readData_main = "データ読み取りエラーが発生しています。"
        const val writeData_main = "データ書き込みエラーが発生しています。"
        const val parameter_main = "パラメータ異常が発生しています。"
        const val filePath_main = "ファイルパス異常が発生しています。"
        const val softOther_main = "ソフト制御異常(その他異常)が発生しています。"

        const val fallPreventionStop_main = "落下の危険性があり緊急停止しました。\n カートを設置しなおしてください。"

        const val LEDFront_main = "LED前面が故障しています。"
        const val LEDBack_main = "LED背面が故障しています。"
        //const val buzzerBack_main = "ブザー背面が故障しています。"
        const val statusLED_main = "ステータスLEDが故障しています。"
        //const val statusLEDBack_main = "ステータスLED背面が故障しています。"
        const val conditionNotifyLED_main = "状態通知LEDが故障しています。"
        const val anomalyTemp_main = "温度異常が発生しています。"

//endregion

//region 警告コードタイトル
        const val maintenanceTimeNotify_title = "メンテナンス時期通知"
        const val batteryExchange_title = "バッテリー交換"
        const val USBCapacityDecrease_title = "USBメモリ容量低下"
        const val unexpectedReceiveNotify_title = "意図せぬ電文"
//endregion

//region 警告コード本文
        const val maintenanceTimeNotify_main = "メンテナンス時期の通知をします。"
        const val batteryExchange_main = "バッテリー交換をしてください。"
        const val USBCapacityDecrease_main = "USBメモリの容量が低下しています。"
        const val unexpectedReceiveNotify_main = "意図しない箇所に電文が送られました。"
//endregion
    }

    override fun onAttach(context: Context) {
        super.onAttach(context)
        try {
            //MainActivity（呼び出し元）をListenerに変換する
            val mainActivity: MainActivity = activity as MainActivity
            listener = mainActivity
        } catch (e: ClassCastException) {
            val errMsg = "Failed to onAttach: ${e.printStackTrace()}"
            Log.d(TAG, errMsg)
            LogExport.dialogExportText(errMsg)
        }
    }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        arguments?.let {
            cartNo = it.getInt(Constants.Id.CART_IDX_ID)
        }
    }

    @SuppressLint("SetTextI18n")
    override fun onCreateView(
        inflater: LayoutInflater, container: ViewGroup?,
        savedInstanceState: Bundle?
    ): View {

        val rootView:View = inflater.inflate(R.layout.fragment_error, container, false)

        val trainNo = viewModel.cartList[cartNo].value!!.train_no
        val side = viewModel.cartList[cartNo].value!!.side
        val errorCode = viewModel.cartList[cartNo].value!!.error_code
        val deviceNo = viewModel.cartList[cartNo].value!!.device_no

        // カートリストのViewModel変更通知設定
        for (i in  viewModel.cartList.indices) {
            val cartDataObserver: Observer<CartData> = Observer<CartData> {
                val icon: ImageView = rootView.findViewWithTag("ErrorIcon${i+1}")
                iconChange(i, icon)
            }
            viewModel.cartList[i].observe(ProcessLifecycleOwner.get(), cartDataObserver)
        }

        val errorCartTextView: TextView = rootView.findViewById(R.id.error_cart_text)
        val trainSide = if(side==Constants.Side.SEA_SIDE) "A" else "D"
        errorCartTextView.text = "【${trainNo}号車・${trainSide}席】\n　機体番号：" + "%04d".format(deviceNo)


        var titleText = "【${errorCode}】対応エラーコード無し"
        var mainText = ""
        for(item in ErrorTextTable.table){
            if(item.code == errorCode){
                titleText = "【${errorCode}】 ${item.title}"
                mainText = item.message
                break
            }
        }

        val errorCodeTextView: TextView = rootView.findViewById(R.id.error_code_text)
        errorCodeTextView.text = titleText

        val mainTextView: TextView = rootView.findViewById(R.id.error_main_text)
        mainTextView.text = mainText

        // クリックイベントの実装

        //確認ボタン
        val confirmationButton: Button = rootView.findViewById(R.id.error_confirmation_button)
        if(errorCode == Constants.ErrorCode.FallPreventionStop){
            confirmationButton.visibility = Button.VISIBLE
            confirmationButton.setOnClickListener {
                listener.confirmationClickFromFragment(cartNo)
            }
        }
        else {
            confirmationButton.visibility = Button.INVISIBLE
        }

        // 戻るボタン
        val backButton: Button = rootView.findViewById(R.id.error_back_button)
        backButton.setOnClickListener {
            listener.backClickFromFragment()
        }
        return rootView
    }

    /**
     * 他カートの状態に応じてエラーアイコン更新処理
     */
    private fun iconChange(cartNo: Int, icon: ImageView){
        try {
            icon.visibility = View.VISIBLE

            var myBitmap: Bitmap = BitmapFactory.decodeResource(resources, R.drawable.none)
            if(viewModel.cartList[cartNo].value!!.is_obstacle){
                myBitmap = BitmapFactory.decodeResource(resources, R.drawable.obstacle)
            }
            if (viewModel.cartList[cartNo].value!!.status == CartStatus.Error){
                myBitmap = BitmapFactory.decodeResource(resources, R.drawable.warning)
            }
            else if(viewModel.cartList[cartNo].value!!.status == CartStatus.DisConnect){
                myBitmap = BitmapFactory.decodeResource(resources, R.drawable.signal_disconnected)
            }
            icon.setImageBitmap(myBitmap)
        }
        catch (e: Exception){
            val errMsg = "Failed to iconChange: ${e.printStackTrace()}"
            Log.d(TAG, errMsg)
            LogExport.dialogExportText(errMsg)
        }
    }
}