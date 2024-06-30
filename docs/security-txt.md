# security.txt

This site includes a [security.txt](https://ben.ramsey.dev/.well-known/security.txt)
file that implements the standard defined in [RFC 9116](https://www.rfc-editor.org/rfc/rfc9116).
More information is available at <https://securitytxt.org>.

RFC 9116 requires an `Expires` field in `security.txt`, and its recommendation
is for the `Expires` field to be less than a year in the future. This provides
security researchers with confidence they are using our most up-to-date reporting
policies.

## Making changes to security.txt

To make changes to `security.txt`:

1. Remove the PGP signature that wraps the body of `security.txt`:

   ```shell
   gpg --decrypt --output templates/security.txt templates/security.txt
   ````

2. Make and save your changes to this file, e.g., update the `Expires` timestamp.

3. Sign your changes:

   ```shell
   gpg --clearsign --local-user YOU@example.com --output templates/security.txt.asc templates/security.txt
   ```

   > [!WARNING]
   > You cannot use `--output` to output the signature to the same file as the
   > input file or `gpg` will result in a signature wrapped around empty content.

4. Last, replace `security.txt` with `security.txt.asc` and commit your changes:

   ```shell
   mv templates/security.txt.asc templates/security.txt
   git commit templates/security.txt
   ```

> [!NOTE]
> You may verify the signature with the following command:
>
> ```shell
> gpg --verify templates/security.txt
> ```
