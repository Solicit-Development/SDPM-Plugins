<?php

/**                                                                        **\
 *               █░█ █▄░█ █▀ █▀█ █░░ █ █▀▀ █ ▀█▀ █▀▀ █▀▄                    *
 *               █▄█ █░▀█ ▄█ █▄█ █▄▄ █ █▄▄ █ ░█░ ██▄ █▄▀                    *
 *                                                                          *
 *                       █▀ ▀█▀ █░█ █▀▄ █ █▀█ █▀                            *
 *                       ▄█ ░█░ █▄█ █▄▀ █ █▄█ ▄█                            *
 *                https://github.com/Unsolicited-Studios                    *
 *                                                                          *
 *                  Copyright 2022 Unsolicited-Studios                      *
 *    Licensed under the Apache License, Version 2.0 (the 'License');       *
 *   you may not use this file except in compliance with the License.       *
 *                                                                          *
 *                You may obtain a copy of the License at                   *
 *              http://www.apache.org/licenses/LICENSE-2.0                  *
 *                                                                          *
 *  Unless required by applicable law or agreed to in writing, software     *
 *   distributed under the License is distributed on an 'AS IS' BASIS,      *
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. *
 *  See the License for the specific language governing permissions and     *
 *                    limitations under the License.                        *
 *                                                                          *
 */                                                                        //

declare(strict_types=1);

namespace UnsolicitedDev\LocationAPI\parser\type;

use UnsolicitedDev\LocationAPI\parser\ParserBase;

class VPNParser extends ParserBase
{
    public static function parseResult(mixed $result, string $ip): bool|string
    {
        if (is_array($result)) {
            return match (true) {
                isset($result['BadIP']) => $result['BadIP'] >= 1 ? true : false, // 1
                isset($result[$ip]['proxy']) => $result[$ip]['proxy'] === 'yes' ? true : false, // 2
                isset($result['host-ip']) => $result['host-ip'], // 4
                isset($result['isProxy']) => $result['isProxy'] === 'YES' ? true : false, // 5
                isset($result['security']['vpn']) => $result['security']['vpn'], // 6
                isset($result['security']['proxy']) => $result['security']['proxy'], // 6
                isset($result['security']['tor']) => $result['security']['tor'], // 6
                isset($result['security']['relay']) => $result['security']['relay'], // 6
                isset($result['vpn']) => $result['vpn'], // 7
                isset($result['proxy']) => $result['proxy'], // 7, 11, 12
                isset($result['tor']) => $result['tor'], // 7
                isset($result['block']) => $result['block'] === 1 ? true : false, // 8
                isset($result['data']['block']) => $result['data']['block'] === 1 ? true : false, // 9
                isset($result['privacy']['vpn']) => $result['privacy']['vpn'], // 10
                isset($result['privacy']['proxy']) => $result['privacy']['proxy'], // 10
                isset($result['privacy']['tor']) => $result['privacy']['tor'], // 10
                isset($result['privacy']['hosting']) => $result['privacy']['hosting'], // 10
                default => self::parseError($result) ?? 'Unknown error'
            };
        }
        if (is_int($result)) {
            // right now, there's only 1 check using this so we can just return the result directly (3)
            return $result === 1 ? true : false;
        }
        if (is_string($result)) {
            // right now, there's only 1 check using this so we can just return the result directly (13)
            return $result === 'Y' ? true : false;
        }
        return 'Invalid result type';
    }

    public static function parseError(mixed $result): ?string
    {
        return match (true) {
            isset($result['message']) => $result['message'], // 1, 2, 6, 7
            isset($result['msg']) => $result['msg'], // 4, 9
            isset($result['response']) => $result['response'], // 5
            isset($result['error']) => $result['error'], // 8
            isset($result['error']['message']) => $result['error']['message'], // 10
            default => null // 12, 13
        };
    }

    public static function parseMapping(string $ip, array $configs): array
    {
        return [
            'api1' => [
                'url' => 'https://check.getipintel.net/check.php?ip=' . $ip . '&format=json&contact=' . self::generateRandom(mt_rand(6, 10)) . '@outlook.de&oflags=b'
            ],
            'api2' => [
                'url' => 'https://proxycheck.io/v2/' . $ip . '?key=' . $configs['api2.key']
            ],
            'api3' => [
                'url' => 'https://api.iptrooper.net/check/' . $ip
            ],
            'api4' => [
                'url' => 'http://api.vpnblocker.net/v2/json/' . $ip . $configs['api4.key']
            ],
            'api5' => [
                'url' => 'https://api.ip2proxy.com/?ip=' . $ip . '&format=json&key=' . $configs['api5.key']
            ],
            'api6' => [
                'url' => 'https://vpnapi.io/api/' . $ip
            ],
            'api7' => [
                'url' => 'https://ipqualityscore.com/api/json/ip/' . (!empty($configs['api7.key']) ? $configs['api7.key'] : '1') . '/' . $ip . '?strictness=' . $configs['api7.strictness'] . '&allow_public_access_points=true&fast=' . $configs['api7.fast'] . '&lighter_penalties=' . $configs['api7.lighter_penalties'] . '&mobile=' . $configs['api7.mobile']
            ],
            'api8' => [
                'url' => 'http://v2.api.iphub.info/ip/' . $ip,
                'header' => ['X-Key: ' . $configs['api8.key']]
            ],
            'api9' => [
                'url' => 'https://www.iphunter.info:8082/v1/ip/' . $ip,
                'header' => ['X-Key: ' . $configs['api9.key']]
            ],
            'api10' => [
                'url' => 'https://ipinfo.io/' . $ip . '/json?token=' . $configs['api10.key']
            ],
            'api11' => [
                'url' => 'https://funkemunky.cc/vpn?ip=' . $ip . '&license=' . $configs['api11.key']
            ],
            'api12' => [
                'url' => 'http://ip-api.com/json/' . $ip . '?fields=proxy'
            ],
            'api13' => [
                'url' => 'https://blackbox.ipinfo.app/lookup/' . $ip
            ],
        ];
    }

    private static function generateRandom(int $charLimit): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $charLimit; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }
        return $randomString;
    }
}
