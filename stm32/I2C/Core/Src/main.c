/* USER CODE BEGIN Header */
/**
  ******************************************************************************
  * @file           : main.c
  * @brief          : Main program body
  ******************************************************************************
  * @attention
  *
  * Copyright (c) 2023 STMicroelectronics.
  * All rights reserved.
  *
  * This software is licensed under terms that can be found in the LICENSE file
  * in the root directory of this software component.
  * If no LICENSE file comes with this software, it is provided AS-IS.
  *
  ******************************************************************************
  */
/* USER CODE END Header */
/* Includes ------------------------------------------------------------------*/
#include "main.h"

/* Private includes ----------------------------------------------------------*/
/* USER CODE BEGIN Includes */

/* USER CODE END Includes */

/* Private typedef -----------------------------------------------------------*/
/* USER CODE BEGIN PTD */

/* USER CODE END PTD */

/* Private define ------------------------------------------------------------*/
/* USER CODE BEGIN PD */
/* USER CODE END PD */

/* Private macro -------------------------------------------------------------*/
/* USER CODE BEGIN PM */

/* USER CODE END PM */

/* Private variables ---------------------------------------------------------*/
I2C_HandleTypeDef hi2c1;

/* USER CODE BEGIN PV */

/* USER CODE END PV */

/* Private function prototypes -----------------------------------------------*/
void SystemClock_Config(void);
static void MX_GPIO_Init(void);
static void MX_I2C1_Init(void);
/* USER CODE BEGIN PFP */
void si5351_write8(uint8_t reg, uint8_t value);
void SetI2C();
/* USER CODE END PFP */

/* Private user code ---------------------------------------------------------*/
/* USER CODE BEGIN 0 */

/* USER CODE END 0 */

/**
  * @brief  The application entry point.
  * @retval int
  */
int main(void)
{
  /* USER CODE BEGIN 1 */

  /* USER CODE END 1 */

  /* MCU Configuration--------------------------------------------------------*/

  /* Reset of all peripherals, Initializes the Flash interface and the Systick. */
  HAL_Init();

  /* USER CODE BEGIN Init */

  /* USER CODE END Init */

  /* Configure the system clock */
  SystemClock_Config();

  /* USER CODE BEGIN SysInit */

  /* USER CODE END SysInit */

  /* Initialize all configured peripherals */
  MX_GPIO_Init();
  MX_I2C1_Init();
  /* USER CODE BEGIN 2 */
  HAL_I2C_Mem_Write();//I2C_MEMADD_SIZE_8BIT
  HAL_I2C_Master_Transmit();

  /* USER CODE END 2 */

  /* Infinite loop */
  /* USER CODE BEGIN WHILE */
  while (1)
  {
    /* USER CODE END WHILE */

    /* USER CODE BEGIN 3 */
  }
  /* USER CODE END 3 */
}

/**
  * @brief System Clock Configuration
  * @retval None
  */
void SystemClock_Config(void)
{
  RCC_OscInitTypeDef RCC_OscInitStruct = {0};
  RCC_ClkInitTypeDef RCC_ClkInitStruct = {0};

  /** Configure the main internal regulator output voltage
  */
  __HAL_RCC_PWR_CLK_ENABLE();
  __HAL_PWR_VOLTAGESCALING_CONFIG(PWR_REGULATOR_VOLTAGE_SCALE3);

  /** Initializes the RCC Oscillators according to the specified parameters
  * in the RCC_OscInitTypeDef structure.
  */
  RCC_OscInitStruct.OscillatorType = RCC_OSCILLATORTYPE_HSI;
  RCC_OscInitStruct.HSIState = RCC_HSI_ON;
  RCC_OscInitStruct.HSICalibrationValue = RCC_HSICALIBRATION_DEFAULT;
  RCC_OscInitStruct.PLL.PLLState = RCC_PLL_NONE;
  if (HAL_RCC_OscConfig(&RCC_OscInitStruct) != HAL_OK)
  {
    Error_Handler();
  }

  /** Initializes the CPU, AHB and APB buses clocks
  */
  RCC_ClkInitStruct.ClockType = RCC_CLOCKTYPE_HCLK|RCC_CLOCKTYPE_SYSCLK
                              |RCC_CLOCKTYPE_PCLK1|RCC_CLOCKTYPE_PCLK2;
  RCC_ClkInitStruct.SYSCLKSource = RCC_SYSCLKSOURCE_HSI;
  RCC_ClkInitStruct.AHBCLKDivider = RCC_SYSCLK_DIV1;
  RCC_ClkInitStruct.APB1CLKDivider = RCC_HCLK_DIV1;
  RCC_ClkInitStruct.APB2CLKDivider = RCC_HCLK_DIV1;

  if (HAL_RCC_ClockConfig(&RCC_ClkInitStruct, FLASH_LATENCY_0) != HAL_OK)
  {
    Error_Handler();
  }
}

/**
  * @brief I2C1 Initialization Function
  * @param None
  * @retval None
  */
static void MX_I2C1_Init(void)
{

  /* USER CODE BEGIN I2C1_Init 0 */

  /* USER CODE END I2C1_Init 0 */

  /* USER CODE BEGIN I2C1_Init 1 */

  /* USER CODE END I2C1_Init 1 */
  hi2c1.Instance = I2C1;
  hi2c1.Init.ClockSpeed = 100000;
  hi2c1.Init.DutyCycle = I2C_DUTYCYCLE_2;
  hi2c1.Init.OwnAddress1 = 0;
  hi2c1.Init.AddressingMode = I2C_ADDRESSINGMODE_7BIT;
  hi2c1.Init.DualAddressMode = I2C_DUALADDRESS_DISABLE;
  hi2c1.Init.OwnAddress2 = 0;
  hi2c1.Init.GeneralCallMode = I2C_GENERALCALL_DISABLE;
  hi2c1.Init.NoStretchMode = I2C_NOSTRETCH_DISABLE;
  if (HAL_I2C_Init(&hi2c1) != HAL_OK)
  {
    Error_Handler();
  }
  /* USER CODE BEGIN I2C1_Init 2 */

  /* USER CODE END I2C1_Init 2 */

}

/**
  * @brief GPIO Initialization Function
  * @param None
  * @retval None
  */
static void MX_GPIO_Init(void)
{

  /* GPIO Ports Clock Enable */
  __HAL_RCC_GPIOB_CLK_ENABLE();

}

/* USER CODE BEGIN 4 */
#define SI5351_ADDRESS (0x60)

enum
{
  SI5351_REGISTER_0_DEVICE_STATUS                       = 0,
  SI5351_REGISTER_1_INTERRUPT_STATUS_STICKY             = 1,
  SI5351_REGISTER_2_INTERRUPT_STATUS_MASK               = 2,
  SI5351_REGISTER_3_OUTPUT_ENABLE_CONTROL               = 3,
  SI5351_REGISTER_9_OEB_PIN_ENABLE_CONTROL              = 9,
  SI5351_REGISTER_15_PLL_INPUT_SOURCE                   = 15,
  SI5351_REGISTER_16_CLK0_CONTROL                       = 16,
  SI5351_REGISTER_17_CLK1_CONTROL                       = 17,
  SI5351_REGISTER_18_CLK2_CONTROL                       = 18,
  SI5351_REGISTER_19_CLK3_CONTROL                       = 19,
  SI5351_REGISTER_20_CLK4_CONTROL                       = 20,
  SI5351_REGISTER_21_CLK5_CONTROL                       = 21,
  SI5351_REGISTER_22_CLK6_CONTROL                       = 22,
  SI5351_REGISTER_23_CLK7_CONTROL                       = 23,
  SI5351_REGISTER_24_CLK3_0_DISABLE_STATE               = 24,
  SI5351_REGISTER_25_CLK7_4_DISABLE_STATE               = 25,
  SI5351_REGISTER_42_MULTISYNTH0_PARAMETERS_1           = 42,
  SI5351_REGISTER_43_MULTISYNTH0_PARAMETERS_2           = 43,
  SI5351_REGISTER_44_MULTISYNTH0_PARAMETERS_3           = 44,
  SI5351_REGISTER_45_MULTISYNTH0_PARAMETERS_4           = 45,
  SI5351_REGISTER_46_MULTISYNTH0_PARAMETERS_5           = 46,
  SI5351_REGISTER_47_MULTISYNTH0_PARAMETERS_6           = 47,
  SI5351_REGISTER_48_MULTISYNTH0_PARAMETERS_7           = 48,
  SI5351_REGISTER_49_MULTISYNTH0_PARAMETERS_8           = 49,
  SI5351_REGISTER_50_MULTISYNTH1_PARAMETERS_1           = 50,
  SI5351_REGISTER_51_MULTISYNTH1_PARAMETERS_2           = 51,
  SI5351_REGISTER_52_MULTISYNTH1_PARAMETERS_3           = 52,
  SI5351_REGISTER_53_MULTISYNTH1_PARAMETERS_4           = 53,
  SI5351_REGISTER_54_MULTISYNTH1_PARAMETERS_5           = 54,
  SI5351_REGISTER_55_MULTISYNTH1_PARAMETERS_6           = 55,
  SI5351_REGISTER_56_MULTISYNTH1_PARAMETERS_7           = 56,
  SI5351_REGISTER_57_MULTISYNTH1_PARAMETERS_8           = 57,
  SI5351_REGISTER_58_MULTISYNTH2_PARAMETERS_1           = 58,
  SI5351_REGISTER_59_MULTISYNTH2_PARAMETERS_2           = 59,
  SI5351_REGISTER_60_MULTISYNTH2_PARAMETERS_3           = 60,
  SI5351_REGISTER_61_MULTISYNTH2_PARAMETERS_4           = 61,
  SI5351_REGISTER_62_MULTISYNTH2_PARAMETERS_5           = 62,
  SI5351_REGISTER_63_MULTISYNTH2_PARAMETERS_6           = 63,
  SI5351_REGISTER_64_MULTISYNTH2_PARAMETERS_7           = 64,
  SI5351_REGISTER_65_MULTISYNTH2_PARAMETERS_8           = 65,
  SI5351_REGISTER_66_MULTISYNTH3_PARAMETERS_1           = 66,
  SI5351_REGISTER_67_MULTISYNTH3_PARAMETERS_2           = 67,
  SI5351_REGISTER_68_MULTISYNTH3_PARAMETERS_3           = 68,
  SI5351_REGISTER_69_MULTISYNTH3_PARAMETERS_4           = 69,
  SI5351_REGISTER_70_MULTISYNTH3_PARAMETERS_5           = 70,
  SI5351_REGISTER_71_MULTISYNTH3_PARAMETERS_6           = 71,
  SI5351_REGISTER_72_MULTISYNTH3_PARAMETERS_7           = 72,
  SI5351_REGISTER_73_MULTISYNTH3_PARAMETERS_8           = 73,
  SI5351_REGISTER_74_MULTISYNTH4_PARAMETERS_1           = 74,
  SI5351_REGISTER_75_MULTISYNTH4_PARAMETERS_2           = 75,
  SI5351_REGISTER_76_MULTISYNTH4_PARAMETERS_3           = 76,
  SI5351_REGISTER_77_MULTISYNTH4_PARAMETERS_4           = 77,
  SI5351_REGISTER_78_MULTISYNTH4_PARAMETERS_5           = 78,
  SI5351_REGISTER_79_MULTISYNTH4_PARAMETERS_6           = 79,
  SI5351_REGISTER_80_MULTISYNTH4_PARAMETERS_7           = 80,
  SI5351_REGISTER_81_MULTISYNTH4_PARAMETERS_8           = 81,
  SI5351_REGISTER_82_MULTISYNTH5_PARAMETERS_1           = 82,
  SI5351_REGISTER_83_MULTISYNTH5_PARAMETERS_2           = 83,
  SI5351_REGISTER_84_MULTISYNTH5_PARAMETERS_3           = 84,
  SI5351_REGISTER_85_MULTISYNTH5_PARAMETERS_4           = 85,
  SI5351_REGISTER_86_MULTISYNTH5_PARAMETERS_5           = 86,
  SI5351_REGISTER_87_MULTISYNTH5_PARAMETERS_6           = 87,
  SI5351_REGISTER_88_MULTISYNTH5_PARAMETERS_7           = 88,
  SI5351_REGISTER_89_MULTISYNTH5_PARAMETERS_8           = 89,
  SI5351_REGISTER_90_MULTISYNTH6_PARAMETERS             = 90,
  SI5351_REGISTER_91_MULTISYNTH7_PARAMETERS             = 91,
  SI5351_REGISTER_092_CLOCK_6_7_OUTPUT_DIVIDER          = 92,
  SI5351_REGISTER_165_CLK0_INITIAL_PHASE_OFFSET         = 165,
  SI5351_REGISTER_166_CLK1_INITIAL_PHASE_OFFSET         = 166,
  SI5351_REGISTER_167_CLK2_INITIAL_PHASE_OFFSET         = 167,
  SI5351_REGISTER_168_CLK3_INITIAL_PHASE_OFFSET         = 168,
  SI5351_REGISTER_169_CLK4_INITIAL_PHASE_OFFSET         = 169,
  SI5351_REGISTER_170_CLK5_INITIAL_PHASE_OFFSET         = 170,
  SI5351_REGISTER_177_PLL_RESET                         = 177,
  SI5351_REGISTER_183_CRYSTAL_INTERNAL_LOAD_CAPACITANCE	= 183
};

void si5351_write8(uint8_t reg, uint8_t value){
	HAL_StatusTypeDef status = HAL_OK;

	while (HAL_I2C_IsDeviceReady(&hi2c1, (uint16_t)(SI5351_ADDRESS<<1), 3, 100) != HAL_OK) { }

	status = HAL_I2C_Mem_Write(&hi2c1,							// i2c handle
    						  (uint8_t)(SI5351_ADDRESS<<1),		// i2c address, left aligned
							  (uint8_t)reg,						// register address
							  I2C_MEMADD_SIZE_8BIT,				// si5351 uses 8bit register addresses
							  (uint8_t*)(&value),				// write returned data to this variable
							  1,								// how many bytes to expect returned
							  100);								// timeout
}

void si5351_read8(uint8_t reg, uint8_t *value)
{
	HAL_StatusTypeDef status = HAL_OK;

	while (HAL_I2C_IsDeviceReady(&hi2c1, (uint16_t)(SI5351_ADDRESS<<1), 3, 100) != HAL_OK) { }

    status = HAL_I2C_Mem_Read(&hi2c1,							// i2c handle
    						  (uint8_t)(SI5351_ADDRESS<<1),		// i2c address, left aligned
							  (uint8_t)reg,						// register address
							  I2C_MEMADD_SIZE_8BIT,				// si5351 uses 8bit register addresses
							  (uint8_t*)(&value),				// write returned data to this variable
							  1,								// how many bytes to expect returned
							  100);								// timeout
}

void SetI2C(){
	//参考:https://github.com/ProjectsByJRP/si5351-stm32
	//設定方法についてはPDFのFigure10とレジスタマップを確認,p21

	//Disable Output
	si5351_write8(SI5351_REGISTER_3_OUTPUT_ENABLE_CONTROL, 0xFF);

	/* Power down all output drivers */
	si5351_write8(SI5351_REGISTER_16_CLK0_CONTROL, 0x80);
	si5351_write8(SI5351_REGISTER_17_CLK1_CONTROL, 0x80);
	si5351_write8(SI5351_REGISTER_18_CLK2_CONTROL, 0x80);
	si5351_write8(SI5351_REGISTER_19_CLK3_CONTROL, 0x80);
	si5351_write8(SI5351_REGISTER_20_CLK4_CONTROL, 0x80);
	si5351_write8(SI5351_REGISTER_21_CLK5_CONTROL, 0x80);
	si5351_write8(SI5351_REGISTER_22_CLK6_CONTROL, 0x80);
	si5351_write8(SI5351_REGISTER_23_CLK7_CONTROL, 0x80);

	/* Set the load capacitance for the XTAL */
	si5351_write8(SI5351_REGISTER_183_CRYSTAL_INTERNAL_LOAD_CAPACITANCE, 3<<6);

	//PLLA
	{
		uint8_t baseaddr = 26;
		uint8_t mult = 32;
		uint32_t num = 0, denom = 1;
		uint32_t P1 = 128 * mult - 512;	/* PLL config register P1 */
		uint32_t P2 = num;	     		/* PLL config register P2 */
		uint32_t P3 = denom;	     	/* PLL config register P3 */

		/* The datasheet is a nightmare of typos and inconsistencies here! */
		si5351_write8( baseaddr,   (P3 & 0x0000FF00) >> 8);
		si5351_write8( baseaddr+1, (P3 & 0x000000FF));
		si5351_write8( baseaddr+2, (P1 & 0x00030000) >> 16);
		si5351_write8( baseaddr+3, (P1 & 0x0000FF00) >> 8);
		si5351_write8( baseaddr+4, (P1 & 0x000000FF));
		si5351_write8( baseaddr+5, ((P3 & 0x000F0000) >> 12) | ((P2 & 0x000F0000) >> 16) );
		si5351_write8( baseaddr+6, (P2 & 0x0000FF00) >> 8)
		si5351_write8( baseaddr+7, (P2 & 0x000000FF));
	}

	//MultiSynth
	{
		uint8_t output = 0;
		uint32_t div = 160, num = 0, denom = 1;
		uint32_t P1 = 128 * div - 512;
		uint32_t P2 = num;
		uint32_t P3 = denom;
		uint8_t baseaddr = SI5351_REGISTER_42_MULTISYNTH0_PARAMETERS_1;

		/* Set the MSx config registers */
		si5351_write8( baseaddr,   (P3 & 0x0000FF00) >> 8);
		si5351_write8( baseaddr+1, (P3 & 0x000000FF));
		si5351_write8( baseaddr+2, (P1 & 0x00030000) >> 16);	/* ToDo: Add DIVBY4 (>150MHz) and R0 support (<500kHz) later */
		si5351_write8( baseaddr+3, (P1 & 0x0000FF00) >> 8);
		si5351_write8( baseaddr+4, (P1 & 0x000000FF));
		si5351_write8( baseaddr+5, ((P3 & 0x000F0000) >> 12) | ((P2 & 0x000F0000) >> 16) );
		si5351_write8( baseaddr+6, (P2 & 0x0000FF00) >> 8);
		si5351_write8( baseaddr+7, (P2 & 0x000000FF));
	}

	//Rdiv
	{
		uint8_t Rreg = SI5351_REGISTER_44_MULTISYNTH0_PARAMETERS_3;
		uint8_t regval;
		si5351_read8(Rreg, &regval);
		regval &= 0x0F;
		uint8_t divider = 0;
		divider &= 0x07;
		divider <<= 4;
		regval |= divider;
		si5351_write8(Rreg, regval);
		uint8_t rDiv = 1;
	}

	/* Reset both PLLs */
	si5351_write8(SI5351_REGISTER_177_PLL_RESET, (1<<7) | (1<<5) );

	//Enable desired output
	si5351_write8(SI5351_REGISTER_3_OUTPUT_ENABLE_CONTROL, 0xFE);//0x00
}
/* USER CODE END 4 */

/**
  * @brief  This function is executed in case of error occurrence.
  * @retval None
  */
void Error_Handler(void)
{
  /* USER CODE BEGIN Error_Handler_Debug */
  /* User can add his own implementation to report the HAL error return state */
  __disable_irq();
  while (1)
  {
  }
  /* USER CODE END Error_Handler_Debug */
}

#ifdef  USE_FULL_ASSERT
/**
  * @brief  Reports the name of the source file and the source line number
  *         where the assert_param error has occurred.
  * @param  file: pointer to the source file name
  * @param  line: assert_param error line source number
  * @retval None
  */
void assert_failed(uint8_t *file, uint32_t line)
{
  /* USER CODE BEGIN 6 */
  /* User can add his own implementation to report the file name and line number,
     ex: printf("Wrong parameters value: file %s on line %d\r\n", file, line) */
  /* USER CODE END 6 */
}
#endif /* USE_FULL_ASSERT */
