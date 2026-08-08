import hashlib
import hmac
import unittest

from utaone_api.security import verify_revenuecat_signature


class SecurityTest(unittest.TestCase):
    def test_revenuecat_hmac_verification(self) -> None:
        payload = b'{"event":{"id":"evt"}}'
        timestamp = 1000
        signature = hmac.new(b"secret", f"{timestamp}.".encode() + payload, hashlib.sha256).hexdigest()
        header = f"t={timestamp},v1={signature}"
        self.assertTrue(verify_revenuecat_signature(payload, header, "secret", now=1000))
        self.assertFalse(verify_revenuecat_signature(payload + b" ", header, "secret", now=1000))
