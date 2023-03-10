/* Copyright (C) 2021 Mono Wireless Inc. All Rights Reserved.    *
 * Released under MW-SLA-*J,*E (MONO WIRELESS SOFTWARE LICENSE   *
 * AGREEMENT).                                                   */

#pragma once

#include "../_tweltite.hpp"
#include "mwx_brd_pal.hpp"
#include "mwx_boards.hpp"

#include "../mwx_twenet.hpp" // if using the_twelite instance.

#include "../sensors/mwx_sns_SHT4x.hpp"

namespace mwx { inline namespace L1 {
	class BrdARIA : MWX_APPDEFS_CRTP(BrdARIA)
	{
	public:
		static const uint8_t TYPE_ID = mwx::BOARD::PAL_AMB;

		// load common definition for handlers
		#define __MWX_APP_CLASS_NAME BrdARIA
		#include "_mwx_cbs_hpphead.hpp"
		#undef __MWX_APP_CLASS_NAME


	public: // PORT ALIASES
		static const uint8_t PIN_WDT = 13; // WDT (shall tick every 60sec)

        static const uint8_t PIN_SNS_NORTH = 16; // for MAGNET Sensor
        static const uint8_t PIN_SNS_OUT1 = 16;
        static const uint8_t PIN_SNS_SOUTH = 8;
        static const uint8_t PIN_SNS_OUT2 = 8;
		
		static const uint8_t PIN_LED = 5;  // LED

		static const uint8_t PIN_SET = 12; // SET PIN

	public: // sensor instances
		SnsSHT4x sns_SHT4x;

	private:
		periph_led_timer _led; // LED Timer handling
		
	public:
		// constructor
		BrdARIA() : _led(PIN_LED) {}

		// called when the object is constructed (similar to setup())
		void on_create(uint32_t& val) {
			if (!Wire._has_begun()) Wire.begin();
			sns_SHT4x.setup();
		}

		// called when the object is started (similar to begin())
		void on_begin(uint32_t& val) {
			// WDT
			pinMode(PIN_WDT, OUTPUT_INIT_HIGH);

			// MAGNET sensor
			pinMode(PIN_SNS_OUT1, PIN_MODE::INPUT);
			pinMode(PIN_SNS_OUT2, PIN_MODE::INPUT);

			// set up LED
			pinMode(PIN_LED, OUTPUT_INIT_HIGH);
			_led.begin();
		}

	public:
		// TWENET callback handler (mandate)
		void loop() {
			if (TickTimer.available()) {
				uint32_t tick = millis();

				// WDT RESET
				digitalWrite(PIN_WDT, (tick & 1) ? HIGH : LOW);

				// LED
				if(_led) _led.tick();
			}
		}

		// called about to sleep
		void on_sleep(uint32_t & val) {
			sns_SHT4x.on_sleep();

			// set high for save sleeping current.
			pinMode(PIN_WDT, OUTPUT_INIT_HIGH); // just in case, if some code set pinMode to different mode.

			// LED
			if (_led) digitalWrite(PIN_LED, HIGH);
		}

		// called during warm booting (very initial stage)
		void warmboot(uint32_t & val) {}

		// called at waking up
		void wakeup(uint32_t & val) {
			pinMode(PIN_WDT, OUTPUT_INIT_LOW); // just in case, if some code set pinMode to different mode.

			// LED
			if (_led) _led.begin();

			// sensor
			sns_SHT4x.wakeup();
		}

		// called when having a message post.
		void on_message(uint32_t& val) { }

	public: // never called the following as hardware class, but define it!
		void network_event(mwx::packet_ev_nwk& pEvNwk) {}
		void receive(mwx::packet_rx& rx) {
			// LED
			if(_led) _led.begin(periph_led_timer::ON_RX);
		}
		void transmit_complete(mwx::packet_ev_tx& pEvTx) {
			// LED
			if(_led) _led.begin(periph_led_timer::ON_TX_COMP);
		}
		
	public: // led functions
		// set led mode (0: none, 1: blink)
		inline void set_led(uint8_t mode, uint16_t tick) {
			_led.setup(mode, tick);
		}

		// note: change mode to LED_TIMER.
		inline void led_one_shot(uint16_t tick) {
			_led.setup(periph_led_timer::ONESHOT, tick);
			_led.begin(periph_led_timer::ONESHOT);
		}
	};

}}
