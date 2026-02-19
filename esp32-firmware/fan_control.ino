/*
 * ESP32 Fan Control Firmware
 * ==========================
 * Controls a fan via PWM based on commands from a Laravel web server.
 * Supports 3 modes:
 *   - Manual:      Server sends speed (0-100%), ESP32 sets PWM
 *   - Voice:       Same as manual (server sends speed from voice commands)
 *   - Temperature: ESP32 reads LM35 sensor, decides speed locally,
 *                  and reports temperature back to the server
 *
 * Required Libraries:
 *   - ArduinoJson (install via Arduino Library Manager)
 *   - WiFi (built-in for ESP32)
 *   - HTTPClient (built-in for ESP32)
 */

#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// =============================================================
// CONFIGURATION - EDIT THESE VALUES
// =============================================================

// WiFi credentials
const char* WIFI_SSID     = "YOUR_WIFI_NAME";       // <-- Change this
const char* WIFI_PASSWORD  = "YOUR_WIFI_PASSWORD";   // <-- Change this

// Laravel server URL (your computer's IP when running php artisan serve --host=0.0.0.0)
const char* SERVER_URL = "http://192.168.1.100:8000"; // <-- Change this

// Pin configuration
const int FAN_PIN  = 18;    // GPIO pin connected to fan MOSFET/driver
const int LM35_PIN = 34;    // GPIO pin connected to LM35 sensor (ADC input-only pin)

// PWM configuration
const int PWM_CHANNEL    = 0;
const int PWM_FREQUENCY  = 25000;  // 25 kHz - good for fan control
const int PWM_RESOLUTION = 8;      // 8-bit: 0-255

// Polling interval (milliseconds)
const unsigned long POLL_INTERVAL = 2000;  // Poll server every 2 seconds

// =============================================================
// GLOBAL VARIABLES
// =============================================================

unsigned long lastPollTime = 0;
String currentMode = "manual";
int currentSpeed = 0;

// =============================================================
// SETUP
// =============================================================

void setup() {
    Serial.begin(115200);
    Serial.println();
    Serial.println("================================");
    Serial.println("ESP32 Fan Control - Starting...");
    Serial.println("================================");

    // Configure fan PWM output
    ledcAttach(FAN_PIN, PWM_FREQUENCY, PWM_RESOLUTION);
    setFanPWM(0);  // Start with fan off

    // Connect to WiFi
    connectWiFi();
}

// =============================================================
// MAIN LOOP
// =============================================================

void loop() {
    // Reconnect WiFi if disconnected
    if (WiFi.status() != WL_CONNECTED) {
        Serial.println("WiFi disconnected! Reconnecting...");
        connectWiFi();
    }

    // Poll server at regular intervals
    unsigned long now = millis();
    if (now - lastPollTime >= POLL_INTERVAL) {
        lastPollTime = now;
        pollServer();
    }
}

// =============================================================
// WIFI CONNECTION
// =============================================================

void connectWiFi() {
    Serial.print("Connecting to WiFi: ");
    Serial.println(WIFI_SSID);

    WiFi.begin(WIFI_SSID, WIFI_PASSWORD);

    int attempts = 0;
    while (WiFi.status() != WL_CONNECTED && attempts < 30) {
        delay(500);
        Serial.print(".");
        attempts++;
    }

    if (WiFi.status() == WL_CONNECTED) {
        Serial.println();
        Serial.print("Connected! IP: ");
        Serial.println(WiFi.localIP());
    } else {
        Serial.println();
        Serial.println("WiFi connection failed! Will retry...");
    }
}

// =============================================================
// SERVER POLLING
// =============================================================

void pollServer() {
    HTTPClient http;
    String url = String(SERVER_URL) + "/api/fan/status";

    http.begin(url);
    int httpCode = http.GET();

    if (httpCode == 200) {
        String payload = http.getString();
        Serial.print("Server response: ");
        Serial.println(payload);

        // Parse JSON response: { "mode": "manual", "speed": 75, "status": "on" }
        JsonDocument doc;
        DeserializationError error = deserializeJson(doc, payload);

        if (error) {
            Serial.print("JSON parse error: ");
            Serial.println(error.c_str());
            http.end();
            return;
        }

        currentMode = doc["mode"].as<String>();
        int serverSpeed = doc["speed"].as<int>();

        Serial.print("Mode: ");
        Serial.print(currentMode);
        Serial.print(" | Server Speed: ");
        Serial.println(serverSpeed);

        // Handle based on current mode
        if (currentMode == "temperature") {
            handleTemperatureMode();
        } else {
            // Manual or Voice mode - follow server's speed command
            handleServerSpeedMode(serverSpeed);
        }

    } else {
        Serial.print("HTTP error: ");
        Serial.println(httpCode);
    }

    http.end();
}

// =============================================================
// MANUAL / VOICE MODE - Follow server speed
// =============================================================

void handleServerSpeedMode(int speedPercent) {
    // Clamp speed to 0-100
    speedPercent = constrain(speedPercent, 0, 100);
    currentSpeed = speedPercent;

    // Map 0-100% to 0-255 PWM
    int pwmValue = map(speedPercent, 0, 100, 0, 255);
    setFanPWM(pwmValue);

    Serial.print("Fan PWM set to: ");
    Serial.print(pwmValue);
    Serial.print(" (");
    Serial.print(speedPercent);
    Serial.println("%)");
}

// =============================================================
// TEMPERATURE MODE - Read LM35 and decide speed locally
// =============================================================

void handleTemperatureMode() {
    // Read temperature from LM35
    float temperature = readLM35();

    Serial.print("Temperature: ");
    Serial.print(temperature, 1);
    Serial.println(" C");

    // Determine fan speed based on temperature thresholds
    int speedPercent = 0;

    if (temperature >= 30.0) {
        speedPercent = 100;  // HIGH
    } else if (temperature >= 26.0) {
        speedPercent = 50;   // MED
    } else if (temperature >= 23.0) {
        speedPercent = 25;   // LOW
    } else {
        speedPercent = 0;    // OFF (below 20 C)
    }

    currentSpeed = speedPercent;

    // Set fan PWM
    int pwmValue = map(speedPercent, 0, 100, 0, 255);
    setFanPWM(pwmValue);

    Serial.print("Auto speed: ");
    Serial.print(speedPercent);
    Serial.print("% (PWM: ");
    Serial.print(pwmValue);
    Serial.println(")");

    // Report temperature back to server for dashboard display
    reportTemperature(temperature);
}

// =============================================================
// LM35 SENSOR READING
// =============================================================

float readLM35() {
    // Read analog value (ESP32 ADC is 12-bit: 0-4095)
    int adcValue = analogRead(LM35_PIN);

    // ESP32 ADC reference voltage is 3.3V
    // LM35 outputs 10mV per degree Celsius
    // Voltage = (adcValue / 4095) * 3.3V
    // Temperature = Voltage / 0.01 = Voltage * 100
    float voltage = (adcValue / 4095.0) * 3.3;
    float temperature = voltage * 100.0;

    // Take average of 10 readings for stability
    float total = 0;
    for (int i = 0; i < 10; i++) {
        int reading = analogRead(LM35_PIN);
        float v = (reading / 4095.0) * 3.3;
        total += v * 100.0;
        delay(10);
    }
    temperature = total / 10.0;

    return temperature;
}

// =============================================================
// REPORT TEMPERATURE TO SERVER
// =============================================================

void reportTemperature(float temperature) {
    HTTPClient http;
    String url = String(SERVER_URL) + "/api/fan/temperature";

    http.begin(url);
    http.addHeader("Content-Type", "application/json");

    // Build JSON body: {"temperature": 27.5}
    JsonDocument doc;
    doc["temperature"] = round(temperature * 10.0) / 10.0;  // Round to 1 decimal

    String jsonBody;
    serializeJson(doc, jsonBody);

    int httpCode = http.POST(jsonBody);

    if (httpCode == 200) {
        Serial.print("Temperature reported: ");
        Serial.print(temperature, 1);
        Serial.println(" C");
    } else {
        Serial.print("Failed to report temperature. HTTP code: ");
        Serial.println(httpCode);
    }

    http.end();
}

// =============================================================
// FAN PWM CONTROL
// =============================================================

void setFanPWM(int pwmValue) {
    // Clamp to valid range
    pwmValue = constrain(pwmValue, 0, 255);
    ledcWrite(FAN_PIN, pwmValue);
}
