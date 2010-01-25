# encoding: utf-8

# =============================================================================
# package info
# =============================================================================
NAME = 'symmetrics_module_invoicepdf'

TAGS = ('magento', 'module', 'symmetrics', 'germanconfig', 'locpack')

LICENSE = 'AFL 3.0'

HOMEPAGE = 'http://www.symmetrics.de'

INSTALL_PATH = ''

# =============================================================================
# responsibilities
# =============================================================================
TEAM_LEADER = {
    'Sergej Braznikov': 'sb@symmetrics.de'
}

MAINTAINER = {
    'Eugen Gitin': 'eg@symmetrics.de'
}

AUTHORS = {
    'Eugen Gitin': 'eg@symmetrics.de'
}

# =============================================================================
# additional infos
# =============================================================================
INFO = 'symmetrics Rechnungsvorlage'

SUMMARY = '''
    Rechtssichere (Deutschland) Vorlage für die Rechnungen
'''

NOTES = '''
'''

# =============================================================================
# relations
# =============================================================================
REQUIRES = [
    {'magento': '*', 'magento_enterprise': '*'}, 
    {'symmetrics_config_german': '*'}, 
]

EXCLUDES = {
}

DEPENDS_ON_FILES = (
    'app/code/core/Mage/Bundle/Model/Sales/Order/Pdf/Items/Invoice.php',
    'app/code/core/Mage/Sales/Model/Order/Pdf/Abstract.php',
    'app/code/core/Mage/Sales/Model/Order/Pdf/Invoice.php',
    'app/code/core/Mage/Sales/Model/Order/Pdf/Creditmemo.php',
    'app/code/core/Mage/Sales/Model/Order/Pdf/Items/Invoice/Default.php',
    'app/code/core/Mage/Sales/Model/Order/Pdf/Items/Creditmemo/Default.php',
)

PEAR_KEY = ''


COMPATIBLE_WITH = {
    'magento': ['1.3.2.3', '1.3.2.4'],
    'magento_enterprise': ['1.3.2.3', '1.3.2.4', '1.7.0.0'],
}
