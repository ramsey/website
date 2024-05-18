<?php

/**
 * This file is part of ramsey/website
 *
 * Copyright (c) Ben Ramsey <ben@ramsey.dev>
 *
 * ramsey/website is free software: you can redistribute it and/or modify it
 * under the terms of the GNU Affero General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.
 *
 * ramsey/website is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with ramsey/website. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace App\Controller;

use App\Util\CacheTtl;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use function base64_decode;
use function strtolower;

/**
 * Implements OpenPGP Web Key Directory
 *
 * @link https://www.ietf.org/archive/id/draft-koch-openpgp-webkey-service-17.html I-D: OpenPGP Web Key Directory
 */
#[AsController]
final readonly class OpenpgpkeyController
{
    /** @noinspection SpellCheckingInspection */
    private const string ZBASE32_PATTERN = '[ybndrfg8ejkmcpqxot1uwisza345h769]{32}';

    #[Route('/.well-known/openpgpkey/policy')]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function policy(Request $request): Response
    {
        return $this->policyWithHostname($request->getHost());
    }

    #[Route('/.well-known/openpgpkey/{hostname}/policy')]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function policyWithHostname(string $hostname): Response
    {
        $response = new Response($this->getPolicyDoc($hostname));
        $response->headers->add([
            'access-control-allow-origin' => '*',
            'content-type' => 'text/plain; charset=utf-8',
        ]);

        return $response;
    }

    /**
     * @param string $id The mapped local-part encoded as a z-base-32 string.
     */
    #[Route('/.well-known/openpgpkey/hu/{id}', requirements: ['id' => self::ZBASE32_PATTERN])]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function key(Request $request, string $id): Response
    {
        return $this->keyWithHostname($request->getHost(), $id);
    }

    /**
     * @param string $id The mapped local-part encoded as a z-base-32 string.
     */
    #[Route('/.well-known/openpgpkey/{hostname}/hu/{id}', requirements: ['id' => self::ZBASE32_PATTERN])]
    #[Cache(maxage: CacheTtl::Week->value, public: true, staleWhileRevalidate: CacheTtl::Day->value)]
    public function keyWithHostname(string $hostname, string $id): Response
    {
        $response = new Response($this->getKey($hostname, $id));
        $response->headers->add([
            'access-control-allow-origin' => '*',
            'content-type' => 'application/octet-stream',
        ]);

        return $response;
    }

    private function getPolicyDoc(string $hostname): string
    {
        return match (strtolower($hostname)) {
            'benramsey.com' => "# Policy flags for domain benramsey.com\n",
            'ramsey.dev' => "# Policy flags for domain ramsey.dev\n",
            default => throw new NotFoundHttpException(),
        };
    }

    private function getKey(string $hostname, string $id): string
    {
        /** @noinspection SpellCheckingInspection */
        return base64_decode(match (strtolower($hostname)) {
            'benramsey.com' => match ($id) {
                'qpui546ptjbsz3rqaetbdz8wj9op6nur' => <<<'EOD'
                    mQMuBFHOFAoRCADRPEtlBvcNSL6KQS4AWuk9Cwp+1AtzU9iUaWDlTbKpwpBpCkC6vXkNdUMuYBXd
                    EvsbBcgJUqBYKxbCgkpNXlOWHhxIxVw3J/OK2qG/AqpTtjDABKByEC7J0QfT/Rz4lc/ttUwLnpNJ
                    J6+g34dSRqyPbWFAzyafCHqae6x7PoA9WmliEhDrD1kuRBHMvZ2fhMtLrRS7f8DobG7328lWlgGl
                    IH9GHrbAVfGPbx040Npg+m66imUfxNEhaMXEPPcD1s3KS6vjj59O/KWwznW1jxwPRw5zTTqt1tuq
                    Re7S8fJmP202B1748x3CmVQMS+TjGHWkKEocuuAZoi8wHot7mo1rAQCejUYdW6L9vaWzlcJF2MAj
                    +VmnBdqMBkRzwo6NY7ItjQf/Q6Xd5RdY14Ivc4Vm/PxVidgJDNGvL+bdwZTLZxHG6aDfQghk5nP/
                    lhRcHgrx5lD2eh9Wv2fa8t8rpv4lyH2dtXM+qw5W2WCfvP+7/xuusm4RqfY16T7tG4cgAkfMljNt
                    Q7utkHLq5tOhZYd8J7PXm04ZxSEO6cMihshiLaP9CKlZZV4IUWFrLSqbhcH+GOHZXhqIFkM4YTal
                    QBH4lrEFSTo7QcqCJPT0FmKtMvoaeufSAkmUk8twgY8yMJBJ+qXXFmqIbEnwhQ+v+DP154YH4WCt
                    TcO4qjZ1+CrkN49XOj5TxGtcUd8kXM1t0Hdc5hRl2CWInNJgngOuWVDjSlgDPAgAnNeMmNYxjJDP
                    4Ldu0/BrB1otitXMtBzva2xK98gpiz2S3VxTJy9JU72SiRjepV0+WUIhMU3o0NkP2hMxQJQBoRdV
                    uC/HRN4xvLBwBI5S/qXq6q5iCZBSNH5FcvNUXjMVTGkS0d9QWD9eK44qTS9CWMY0j1tU8qJMs159
                    v+cyG4QnJdxS5Rd96JfCp1GMHLok2/YTsc5bH2Mj5BPb3AC40IP7eJxnB3Sa7m8jvMaP5jtrLpxp
                    dM9w5MIor7sc9pwEkoMhzJtwsdJ6zLQuyVhBaBJ+4MAg5nF7dAmAmYCL18x1iTyS6GUNOlyIEt3S
                    N2R5zkIujPW6LdAxrwkUEJFYirQeQmVuIFJhbXNleSA8YmVuQGJlbnJhbXNleS5jb20+iJoEExEI
                    AEICGwMGCwkIBwMCBhUIAgkKCwQWAgMBAh4BAheAAhkBFiEE6F0DEd36W6zzphDnsC2XmJyMBxsF
                    AmCHOwsFCRV3wwkACgkQsC2XmJyMBxu8dwEAla2tbcNbNBexmAcvHIS2ppXkw1+Xv3FmEZeiPJeU
                    ZSgA/19Os2yYrp+9HSRMs7K0l0pD0dC76L2lW9ZuB1WB37CtuQINBFHOFAoQCACunVdnnjbX0n3j
                    Mk0mDI1NMwBQg65r8SI3vLwHowof8gBJAU7Rp3K+oAxnsiiJ9Pud/0IsXCYKmJtDkXdN1u7g+XkN
                    Ry8d4yOHjHe7KSzfDomar8n1lTvsR2iRaxqZMSmIf1gEjc4sYa1slG1z8ufW+/UPYuZFlyHBiMew
                    KN8B7KK3bMPes5Q9gNO0Whj+v4UnWEVhCc4LwDLQeJ0NmCNSYecMmL0mA9+q4Q8+2aCfAt9p9mYy
                    cDhBeuQMva2Y/6K4alTXryBIVwIHMGgBrCDIwlrY14sq9Gmz8P5palTriWDUziIzNGeGVP9zk8iL
                    iOWavk4ZN+r/798TrZmvPhofAAMFCACUsw8TI8iCerlHJ1uC+MYPb5wKHnK2jbffU46nF+eXiIL1
                    eoCgpux8NpyFMQJaZnC7cL1sdLVcKrYcdhXlFR6uyMMl5OSc+q9dW7M8FqpIFbfKQLyavBuvkSYm
                    40oT2VsM7LQmn4cjHX24mK+pMqPIEukrC4AOnVLZlvB1rai+cQBrVSGNOUYbPlx2ksIzb7D+Qui1
                    qUqg3pDXUq52K3wogYx7CaqgDc4nGZO9ydQDSMWUTTqXxSZmbs+7fIa2wROLuITX+jUp687iZBcX
                    NDUNUpw312kBL3kg94klrQ9xSF7SWe1r1KtrasBI0TlDs6g38YC6Yeo7CrXBuCuT3ZYHiGEEGBEI
                    AAkFAlHOFAoCGwwACgkQsC2XmJyMBxsLWQD9F0eXuAeUKE0K99iq3mRlIy32dC84B56TEwpHwPVm
                    Uy0BAIkamZV1IYP//i6TO3O1EZvoqcMiOm/0HIF9TZZZMrtLiGEEMBEIAAkFAlOXFE4CHSAACgkQ
                    sC2XmJyMBxsBIAD+MdacDv0vVfT1XhmBghVbgpH90GkhCAVtrjaz4l81W+EBAIc8ZYFd1Cbq0vt4
                    4+OL3zFTKY7Lgl6wTqjM+prF9/giiHgEMBEIACAWIQToXQMR3fpbrPOmEOewLZeYnIwHGwUCYIdA
                    RgIdAAAKCRCwLZeYnIwHG1rpAQCCJCySHL1lfZepLKpYmSWJDGtxUJT6krrqcaBTQGIsMgD/eKy/
                    cUL4rdGnxvKuDe1DCPV4kvtWUNq+wvvDk9oGAAKISQQgEQIACQUCUc4PUQIdAQAKCRCXF6fQ2/qY
                    lBsuAKCJjHXPV3eysk6ht/MGwhcs8W2EMgCg0E0Iq87raRnHJu6LgWtA24gjB7aIYAQwEQIAIAUC
                    T9jLWRkdIFVzZXIgSUQgbm8gbG9uZ2VyIHVzZWQuAAoJEJcXp9Db+piUjucAnRKUz5VzmDv/fXe2
                    HFk+Cl0L6ASuAJsFsZVYuaI5bdjTwdEMJrn6PEzkiIhJBDARAgAJBQJLR54PAh0AAAoJEJcXp9Db
                    +piUn5MAn0E5nIaBEHHF1+oSmMzwjsrP5s5dAJ49WgxnL6K87IBdNa6Fa3fUnkcJeohgBDARAgAg
                    BQJP2MdVGR0gUmVtb3ZpbmcgcGhvdG8gZnJvbSBrZXkACgkQlxen0Nv6mJSFtACfY9kl6wtKC8TS
                    74Kg388OOjQ7vjYAnjyChejOOSLt7cQ730PKa0WVbq6hiGAEMBECACAFAk/Yx3IZHSBSZW1vdmlu
                    ZyBwaG90byBmcm9tIGtleQAKCRCXF6fQ2/qYlGp2AJwLGS5S/3gS1RrwVB241LFUj7FZ5QCgjrhI
                    f9bid8LnAjBQm/F0i3nkhPuISQQgEQIACQUCQbsFhwIdAwAKCRDNfCElOWi17o87AJ9Dkf5PF5Vu
                    F4PuXbap5VitfJWgZgCfVoLnWmFXVwq3pAnagSaJAC6PwjuISQQgEQIACQUCQTCjegIdAwAKCRDX
                    +BOX2lc4tzOEAKDC7yjqa9tLkL+G22Z0wyb7U02EAACg4N9djAdjhv7Pf/9OS4N5qMSCnfs=
                    EOD,
                default => throw new NotFoundHttpException(),
            },
            'ramsey.dev' => match ($id) {
                'qpui546ptjbsz3rqaetbdz8wj9op6nur' => <<<'EOD'
                    mQMuBFHOFAoRCADRPEtlBvcNSL6KQS4AWuk9Cwp+1AtzU9iUaWDlTbKpwpBpCkC6vXkNdUMuYBXd
                    EvsbBcgJUqBYKxbCgkpNXlOWHhxIxVw3J/OK2qG/AqpTtjDABKByEC7J0QfT/Rz4lc/ttUwLnpNJ
                    J6+g34dSRqyPbWFAzyafCHqae6x7PoA9WmliEhDrD1kuRBHMvZ2fhMtLrRS7f8DobG7328lWlgGl
                    IH9GHrbAVfGPbx040Npg+m66imUfxNEhaMXEPPcD1s3KS6vjj59O/KWwznW1jxwPRw5zTTqt1tuq
                    Re7S8fJmP202B1748x3CmVQMS+TjGHWkKEocuuAZoi8wHot7mo1rAQCejUYdW6L9vaWzlcJF2MAj
                    +VmnBdqMBkRzwo6NY7ItjQf/Q6Xd5RdY14Ivc4Vm/PxVidgJDNGvL+bdwZTLZxHG6aDfQghk5nP/
                    lhRcHgrx5lD2eh9Wv2fa8t8rpv4lyH2dtXM+qw5W2WCfvP+7/xuusm4RqfY16T7tG4cgAkfMljNt
                    Q7utkHLq5tOhZYd8J7PXm04ZxSEO6cMihshiLaP9CKlZZV4IUWFrLSqbhcH+GOHZXhqIFkM4YTal
                    QBH4lrEFSTo7QcqCJPT0FmKtMvoaeufSAkmUk8twgY8yMJBJ+qXXFmqIbEnwhQ+v+DP154YH4WCt
                    TcO4qjZ1+CrkN49XOj5TxGtcUd8kXM1t0Hdc5hRl2CWInNJgngOuWVDjSlgDPAgAnNeMmNYxjJDP
                    4Ldu0/BrB1otitXMtBzva2xK98gpiz2S3VxTJy9JU72SiRjepV0+WUIhMU3o0NkP2hMxQJQBoRdV
                    uC/HRN4xvLBwBI5S/qXq6q5iCZBSNH5FcvNUXjMVTGkS0d9QWD9eK44qTS9CWMY0j1tU8qJMs159
                    v+cyG4QnJdxS5Rd96JfCp1GMHLok2/YTsc5bH2Mj5BPb3AC40IP7eJxnB3Sa7m8jvMaP5jtrLpxp
                    dM9w5MIor7sc9pwEkoMhzJtwsdJ6zLQuyVhBaBJ+4MAg5nF7dAmAmYCL18x1iTyS6GUNOlyIEt3S
                    N2R5zkIujPW6LdAxrwkUEJFYirQbQmVuIFJhbXNleSA8YmVuQHJhbXNleS5kZXY+iJYEExEIAD4W
                    IQToXQMR3fpbrPOmEOewLZeYnIwHGwUCZQpKtAIbAwUJFXfDCQULCQgHAgYVCgkICwIEFgIDAQIe
                    AQIXgAAKCRCwLZeYnIwHG1WLAP0Zd/nh3Y9sg8+uTyWTu7fhEj5gc8oZAn4/YUNsPQTSkAD/aCfC
                    DRrEwU0/PhS/6lhyf9bEv8p2M/4+kC0oQu+Ns125Ag0EUc4UChAIAK6dV2eeNtfSfeMyTSYMjU0z
                    AFCDrmvxIje8vAejCh/yAEkBTtGncr6gDGeyKIn0+53/QixcJgqYm0ORd03W7uD5eQ1HLx3jI4eM
                    d7spLN8OiZqvyfWVO+xHaJFrGpkxKYh/WASNzixhrWyUbXPy59b79Q9i5kWXIcGIx7Ao3wHsords
                    w96zlD2A07RaGP6/hSdYRWEJzgvAMtB4nQ2YI1Jh5wyYvSYD36rhDz7ZoJ8C32n2ZjJwOEF65Ay9
                    rZj/orhqVNevIEhXAgcwaAGsIMjCWtjXiyr0abPw/mlqVOuJYNTOIjM0Z4ZU/3OTyIuI5Zq+Thk3
                    6v/v3xOtma8+Gh8AAwUIAJSzDxMjyIJ6uUcnW4L4xg9vnAoecraNt99TjqcX55eIgvV6gKCm7Hw2
                    nIUxAlpmcLtwvWx0tVwqthx2FeUVHq7IwyXk5Jz6r11bszwWqkgVt8pAvJq8G6+RJibjShPZWwzs
                    tCafhyMdfbiYr6kyo8gS6SsLgA6dUtmW8HWtqL5xAGtVIY05Rhs+XHaSwjNvsP5C6LWpSqDekNdS
                    rnYrfCiBjHsJqqANzicZk73J1ANIxZRNOpfFJmZuz7t8hrbBE4u4hNf6NSnrzuJkFxc0NQ1SnDfX
                    aQEveSD3iSWtD3FIXtJZ7WvUq2tqwEjROUOzqDfxgLph6jsKtcG4K5PdlgeIYQQYEQgACQUCUc4U
                    CgIbDAAKCRCwLZeYnIwHGwtZAP0XR5e4B5QoTQr32KreZGUjLfZ0LzgHnpMTCkfA9WZTLQEAiRqZ
                    lXUhg//+LpM7c7URm+ipwyI6b/QcgX1Nllkyu0uIYQQwEQgACQUCU5cUTgIdIAAKCRCwLZeYnIwH
                    GwEgAP4x1pwO/S9V9PVeGYGCFVuCkf3QaSEIBW2uNrPiXzVb4QEAhzxlgV3UJurS+3jj44vfMVMp
                    jsuCXrBOqMz6msX3+CKIeAQwEQgAIBYhBOhdAxHd+lus86YQ57Atl5icjAcbBQJgh0BGAh0AAAoJ
                    ELAtl5icjAcbWukBAIIkLJIcvWV9l6ksqliZJYkMa3FQlPqSuupxoFNAYiwyAP94rL9xQvit0afG
                    8q4N7UMI9XiS+1ZQ2r7C+8OT2gYAAg==
                    EOD,
                't5s8ztdbon8yzntexy6oz5y48etqsnbb' => <<<'EOD'
                    mQINBF+Z9gEBEACbT/pIx8RR0K18t8Z2rDnmEV44YdT7HNsMdq+D6SAlx8UUb6AUjGIbV9dgBgGN
                    tOLU1pxloaJwL9bWIRbj+X/Qb2WNIP//Vz1Y40ox1dSpfCUrizXxkb4p58Xml0PsB8dg3b4RDUgK
                    wGC37ne5xmDnigyJPbiB2XJ6Xc46oPCjh86XROTKwEBB2lY67ClBlSlvC2V9KmbTboRQkLdQDhOa
                    UosMb99zRb0EWqDLaFkZVjY5HI7i0pTveE6dI12NfHhTwKjZ5pUiAZQGlKA6J1dMjY2unxHZkQj5
                    MlMfrLSyJHZxccdJxD94T6OTcTHt/XmMpI2AObpewZDdChDQmcYDZXGfAhFoJmbvXsmLMGXKgzKo
                    Z/lsRmLsQhh7+/r8E+Pn5r+A6Hh4uAc14ApyEP0ckKeIXw1C6pepHM4E8TEXVr/IA6K/z6jlHORi
                    xIFX7iNOnfHh+qwOgZw40D6JnBfEzjFi+T2Cy+JzN2uy7I8UnecTMGo35t6astPy6xcH6kZYzFTV
                    7XERR6LIIVyLAiMFd8kF5MbJ8N5ElRFsFHPW+82N2HDXc60iSaTB85k6R6xd8JIKDiaKE4sSuw2w
                    HFCKq33d/GamYezp1wO+bVUQg88efljC2JNFyD+vl30josqhw1HcmbE1TP3DlYeIL5jQOlxCMsga
                    i6JtTfHFM/5MYwARAQABtBNzZWN1cml0eUByYW1zZXkuZGV2iQJUBBMBCAA+FiEE4drPD+/ofZ57
                    0fAYq0bvvXQCywIFAl+Z9gECGwMFCQeGH4AFCwkIBwIGFQoJCAsCBBYCAwECHgECF4AACgkQq0bv
                    vXQCywIkEA//Qcwv8MtTCy01LHZd9c7VslwhNdXQDYymcTyjcYw8x7O22m4B3hXE6vqAplFhVxxk
                    qXB2ef0tQuzxhPHNJgkCE4Wq4i+V6qGpaSVHQT2W6DN/NIhLvS8OdScc6zddmIbIkSrzVVAtjweh
                    FNEIrX3DnbbbK+Iku7vsKT5EclOluIsjlYoXgoW8IeReyDBqOe2H3hoCGw6EA0D/NYV2bJnfy53r
                    XVIyarsXXeOLp7eNEH6Td7aWPVSrMZJe1t+knrEGnEdrXWzlg4lCJJCtemGv+pKBUomnyISXSdqy
                    oRCCzvQjqyig2kRebUX8BXPW33p4OXPj9sIboUOjZwormWwqqbFMO+J4TiVCUoEoheI7emPFRcNN
                    QtPJrjbY1++OznBc0GRpfeUkGoU1cbRl1bnepnFIZMTDLkrVW6I1Y4q8ZVwX3BkEN81ctFrRpHBl
                    U36EdHvjPQmGtuiL77Qq3fWmMv7yTvK1wHJAXfEb0ZJWHZCbck3wl0CVq0Z+UUAOM8Rp1N0N8m92
                    xtapav0qCFU9qzf2J5qX6GRmWv+d29wPgFHzDWBmnnrYYIA4wJLx00U6SMcVBSnNe91B+RfGY5XQ
                    hbWPjQQecOGCSDsxaFAq2MeOVJyZbIjLYfG9GxoLKr5R7oLRJvZI4nKKBc1Kci/crZbdiSdQhSQG
                    lDz88F1OHeCIdQQQEQgAHRYhBOhdAxHd+lus86YQ57Atl5icjAcbBQJfmfdIAAoJELAtl5icjAcb
                    FVcA/1LqB3ZjsnXDAvvAXZVjSPqofSlpMLeRQP6IM/A9Odq0AQCZrtZc1knOMGEcjppKRk+sy/R0
                    Mshy8TDuaZIRgh2Ux7kCDQRfmfYBARAAmchKzzVz7IaEq7PnZDb3szQsT/+E9F3m39yOpV4fEB1Y
                    zObonFakXNT7Gw2tZEx0eitUMqQ/13jjfu3UdzlKl2bRqA8LrSQRhB+PTC9A1XvwxCUYhhjGiLzJ
                    9CZL6hBQB43qHOmE9XJPme90geLsF+gKu39Waj1SNWzwGg+Gy1Gl5f2AJoDTxznreCuFGj+Vfacz
                    t/hlfgqpOdb9jsmdoE7t3DSWppA9dRHWwQSgE6J28rR4QySBcqyXS6IMykqaJn7Z26yNIaITLnHC
                    ZOSY8zhPha7GFsN549EOCgECbrnPt9dmI2+hQE0RO0e7SOBNsIf5sz/i7urhwuj0CbOqhjc2X1AE
                    VNFCVcb6HPi/AWefdFCRu0gaWQxn5g+9nkq5slEgvzCCiKYzaBIcr8qR6Hb4FaOPVPxO8vndRouq
                    57Ws8XpAwbPttioFuCqF4u9K+tK/8e2/R8QgRYJsE3Cz/Fu8+pZFpMnqbDEbK3DL3ss+1ed1sky+
                    mDV8qXXeI33XW5hMFnk1JWshUjHNlQmE6ftCU0xSTMVUtwJhzH2zDp8lEdu7qi3EsNULOl68ozDr
                    6soWAvCbHPeTdTOnFySGCleG/3TonsoZJs/sSPPJnxFQ1DtgQL6EbhIwa0ZwU4eKYVHZ9tjxuMX3
                    teFzRvOrJjgs+ywGlsIURtEckT5Y6nMAEQEAAYkCPAQYAQgAJhYhBOHazw/v6H2ee9HwGKtG7710
                    AssCBQJfmfYBAhsMBQkHhh+AAAoJEKtG7710AssC8NcP/iDAcy1aZFvkA0EbZ85pi7/+ywtE/1wF
                    4U4/9OuLcoskqGGnl1pJNPooMOSBCfreoTB8HimT0Fln0CoaOm4QpScNq39JXmf4VxauqUJVARBy
                    P6zUfgYarqoaZNeuFF0S4AZJ2HhGzaQPjDz1uKVMPE6tQSgQkFzdZ9AtRA4vElTH6yRAgmepUsOi
                    hk0b0gUtVnwtRYZ8e0Qt3ie97a73DxLgAgedFRUbLRYiT0vNaYbainBsLWKpN/T8odwIg/smP0Kh
                    jp/ckV60cZTdBiPRszBTPJESMUTu0VPntc4gWwGsmhZJg/Tt/qP08XYo3VxNYBegyuWwNR66zDWv
                    wvGHmuMv5UchuDxp6Rt3JkIO4voMT1JSjWy9p8krkPEE4V6PxAagLjdZSkt92wVLiK5xy5gNrtPh
                    U45YdRAKHr36OvJBJQ42CDaZ6nzrzghcIp9CZ7ANHrI+QLRM/csz+AGAszSp6S4mc1lnxxfbOhPP
                    pebZPn0nIAXoZnnoVKdrxBVedPQHT59ZFvKTQ9Fs7gd3sYNuc7tJGFGC2CxBH4ANDpOQkc5q9JJ1
                    HSGrXU3juxIiRgfA26Q22S9c71dXjElwRi584QH+bL6kkYmm8xpKF6TVwhwu5xx/jBPrbWqFrtbv
                    LNrnfPoapTihBfdIhkT6nmgawbBHA02D5xEqB5SU3WJu
                    EOD,
                default => throw new NotFoundHttpException(),
            },
            default => throw new NotFoundHttpException(),
        });
    }
}
