<?php

use Hooks;

class Hook {

	/**
	 * Hook to set up the extension.
	 *
	 * @SuppressWarnings("CamelCaseVariableName")
	 * @SuppressWarnings("SuperGlobals")
	 */
	public static function initExtension() {
		$extensionData = \ExtensionRegistry::getInstance()->getAllThings();
		$prefix = '';
		$configAutoLogin = 'PluggableAuth_AutoLogin';
		if ( isset( $extensionData['PluggableAuth'] ) ) {
			$pluggablAuthVersion = $extensionData['PluggableAuth']['version'];
			if ( version_compare( $pluggablAuthVersion, '2.0', '>=' ) ) {
				// PluggableAuth 2.0 prefixed config Variables with 'wg'
				// and renamed '...AutoLogin' to '...EnableAutoLogin'
				$prefix = 'wg';
				$configAutoLogin = 'PluggableAuth_EnableAutoLogin';
			}
		}

		wfDebugLog( __METHOD__, "initializing" );
		$GLOBALS[$prefix . 'PluggableAuth_Timeout'] = 0;
		$GLOBALS[$prefix . $configAutoLogin] = true;
	}
}

?>
