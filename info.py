# encoding: utf-8

# =============================================================================
# package info
# =============================================================================
NAME = 'symmetrics_module_invoicepdf'

TAGS = ('magento', 'module', 'symmetrics', 'pdf', 'invoice', 'german')

LICENSE = 'AFL 3.0'

HOMEPAGE = 'http://www.symmetrics.de'

INSTALL_PATH = ''

# =============================================================================
# responsibilities
# =============================================================================
TEAM_LEADER = {
    'Torsten Walluhn': 'tw@symmetrics.de',
}

MAINTAINER = {
    'Torsten Walluhn': 'tw@symmetrics.de',
}

AUTHORS = {
    'Torsten Walluhn': 'tw@symmetrics.de',
    'Eugen Gitin': 'eg@symmetrics.de',
    'Eric Reiche': 'er@symmetrics.de',
}

# =============================================================================
# additional infos
# =============================================================================
INFO = 'symmetrics Rechnungsvorlage'

SUMMARY = '''
    Rechtssichere (Deutschland) Vorlage für die Rechnungen
'''

NOTES = '''
Wenn noch eine alte Version installiert ist, sollten die Dateien unter
app/code/local/Symmetrics/InvoicePdf entfernt werden, bevor die neue Version
installiert wird.
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
    'app/code/core/Mage/Tax/Model/Sales/Pdf/Grandtotal.php',
)

PEAR_KEY = ''


COMPATIBLE_WITH = {
    'magento': ['1.4.0.0'],
    'magento_enterprise': ['1.7.0.0-rc1'],
}
