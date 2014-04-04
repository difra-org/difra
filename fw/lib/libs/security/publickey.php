<?php

namespace Difra\Libs\Security;

class Publickey {

	public static function get() {

		$key = <<<PKEY
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEA0OHU4Kaqwx9sHe/hPeWU
W91kksMi+FJr+G7Qhk77BKorE4+djGJLPySztP1Ygl0Yh2z0BzXHnZDQRjLz8g4X
m8m4PZWsuhBKQz+uR0/COe184tygHo/jLYTCICdNRSdbwTlj46hTvJeaeixG3YxO
LBfVAI6OKg+Lr6E3Jw+JtxOTQWIjkHbUr00+h+XdGNdNBUkK0VAltyhk4KWHvczm
AOJBxRCnbYj2OAe3IufwTq5B3InyYtAyGf+yq+pIW+LhIQPGhFfpeWtKshhZKNmq
lFiwoRMzvWpa1pRG0pWywtdmyRRZNUHpznceGCGa8NUhziUXhT1Nuo3jSf04Zzc7
wWCqbbdPBsPm1hK3qTc2wIjlxuq5B5SxnRZj/tCsOGdxJnzBOZr8zzmoTV4rjSMw
P2uf+q9T/Qq7gRBP5hOg6T86QjQrYCk270xDS1U/S4NeX6EQKWcATKc9E2Y4Cr8i
+QoZgueH91/zHjzAAuBPFkJukrz2UfqGfSyWmYlDldybj9VKkmwxC5UvepIr4cFU
miQBJ/SvX2UOdddODYfnc2f3lXCNxpemnfyqGocIdTVzp/cJv+FtAje6hXN8QyFD
mq67gm7sTNPlWuAoaf+ITjTrWT2kZAyVrjb0E6Cm4/X+uhSEISPUdxiHBmWkNjZ5
zK+Y0wSlo6GchGaQDDz2EhkCAwEAAQ==
-----END PUBLIC KEY-----
PKEY;

		return $key;
	}
}