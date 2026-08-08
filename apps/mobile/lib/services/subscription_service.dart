import 'dart:io';

import 'package:purchases_flutter/purchases_flutter.dart';

class SubscriptionService {
  static const entitlementId = 'premium';

  Future<void> configure({String? appUserId}) async {
    final key = Platform.isIOS
        ? const String.fromEnvironment('REVENUECAT_IOS_API_KEY')
        : const String.fromEnvironment('REVENUECAT_ANDROID_API_KEY');
    if (key.isEmpty) return;
    await Purchases.setLogLevel(LogLevel.info);
    final configuration = PurchasesConfiguration(key);
    if (appUserId != null) configuration.appUserID = appUserId;
    await Purchases.configure(configuration);
  }

  Future<bool> isPremium() async {
    if (!await Purchases.isConfigured) return false;
    final info = await Purchases.getCustomerInfo();
    return info.entitlements.all[entitlementId]?.isActive == true;
  }

  Future<bool> purchaseCurrentOffering() async {
    final offerings = await Purchases.getOfferings();
    final package = offerings.current?.availablePackages.firstOrNull;
    if (package == null) throw StateError('購入可能なプランがありません。');
    final result = await Purchases.purchase(PurchaseParams.package(package));
    return result.customerInfo.entitlements.all[entitlementId]?.isActive == true;
  }

  Future<bool> restore() async {
    final info = await Purchases.restorePurchases();
    return info.entitlements.all[entitlementId]?.isActive == true;
  }
}
