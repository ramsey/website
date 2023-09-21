# ben.ramsey.dev

## Web Key Directory

To generate the files in `public/.well-known/openpgpkey`, I ran:

```bash
mkdir -p public/.well-known/openpgpkey/ \
    && cd public/.well-known/ \
    && gpg --list-options show-only-fpr-mbox -k "@ramsey.dev" \
        | gpg-wks-client -v --install-key
```

For more information on Web Key Directory, see:

* <https://wiki.gnupg.org/WKD>
* <https://docs.keyoxide.org/advanced/web-key-directory/>
* <https://datatracker.ietf.org/doc/draft-koch-openpgp-webkey-service/>

To force these files to have the `Content-Type` header value of
`application/octet-stream`, I have added `.htaccess` files to their respective
`/hu/` directories with the following:

```apacheconf
ForceType "application/octet-stream"
```
