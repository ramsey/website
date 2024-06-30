# Generating Woff2 Files From TTF

To generate `*.woff2` font files from `*.ttf` font files, use `woff2_compress`.
It's available [on GitHub](https://github.com/google/woff2) or via Homebrew
(with `brew install woff2`).

Run:

```bash
find . -name "*.ttf" -type f | xargs -L 1 woff2_compress
```

Then, copy the `*.woff2` files into `assets/media/fonts/`.
