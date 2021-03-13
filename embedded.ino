#include "DHT.h"
#include <SPI.h>
#include <Ethernet.h>
byte mac[] = { 0xDE, 0xAD, 0xBE, 0xEF, 0xFE, 0xED }; //Setting MAC Address

#define DHTPIN 2
#define DHTTYPE DHT11 //your sensor library
DHT dht(DHTPIN,DHTTYPE);

float humidityData;
float temperatureData;

char server[] = "192.168.178.48"; //your IP Adress
IPAddress ip(192,168,178,177);
EthernetClient client;

void setup() {
  Serial.begin(9600);
  dht.begin();
  if (Ethernet.begin(mac) == 0) {
  Serial.println("Failed to configure Ethernet using DHCP");
  Ethernet.begin(mac, ip);
  }
  delay(1000);
}
//------------------------------------------------------------------------------

void loop(){
  humidityData = dht.readHumidity();
  temperatureData = dht.readTemperature();
  Sending_To_phpmyadmindatabase();
  delay(30000); // interval
}


  void Sending_To_phpmyadmindatabase()
 {
   if (client.connect(server, 80)) { //your server client
    Serial.println("connected");
    // Make a HTTP request:
    Serial.print("GET /192.168.178.48/dht.php?temperature=");
    client.print("GET /192.168.178.48/dht.php?temperature=");     //YOUR URL
    Serial.println(temperatureData);
    client.print(temperatureData);
    client.print("&humidity=");
    Serial.println("&humidity=");
    client.print(humidityData);
    Serial.println(humidityData);
    client.print(" ");
    client.print("HTTP/1.1");
    client.println();
    client.println("Host: 192.168.178.48");
    client.println("Connection: close");
    client.println();
  } else {
    Serial.println("connection failed");
  }
 }
