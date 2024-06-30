# OpenPGP Web Key Directory

The keys in `data/openpgp_web_key.php` are organized by hostname and local part.
The local parts are hashed with SHA-1, and the raw binary format of the hash is
encoded with the Z-Base-32 algorithm described in
[RFC 6189 (section 5.1.6)](https://www.rfc-editor.org/rfc/rfc6189.html#section-5.1.6).

You may determine the local part hash with the following command, or you may
use the later command below, which outputs files named with the local part
hash.

```shell
gpg-wks-client print-wkd-hash you@example.org
```

The keys stored here are base-64 encoded versions of the binary output from
exporting the key:

```shell
mkdir openpgpkey/
gpg --list-options show-only-fpr-mbox -k KEY-ID \
    | gpg-wks-client --install-key
```

This command will output one or more keys (depending on the number of local
part and domain combinations that are associated with the `KEY-ID`), storing
them to the `openpgpkey/` directory in a structure similar to the following:

```
openpgpkey
├── hostname1
│   ├── hu
│   │   └── local_part_hash
│   └── policy
└── hostname2
    ├── hu
    │   └── local_part_hash
    └── policy
```

You may then convert the contents of these files to base-64 encoding and
store them in the array found in `data/openpgp_web_key.php`.

```shell
cat openpgpkey/hostname1/hu/local_part_hash | base64
```

Afterward, you may delete the `openpgpkey/` directory.

## Testing

To test the WKD, use <https://gitlab.com/wiktor/wkd-checker> or use:

```shell
gpg --locate-keys --auto-key-locate clear,nodefault,wkd me@example.com
```

## More Information

For more information on Web Key Directory, see:

* <https://wiki.gnupg.org/WKD>
* <https://www.uriports.com/blog/setting-up-openpgp-web-key-directory/>
* <https://docs.keyoxide.org/wiki/web-key-directory/>
* <https://datatracker.ietf.org/doc/draft-koch-openpgp-webkey-service/>
* <https://teknikaldomain.me/post/pgp-key-discovery-mechanisms-explained/>
