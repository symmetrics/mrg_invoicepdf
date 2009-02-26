# -- encoding: utf-8 --

# package info
name = 'symmetrics_module_invoicepdf'
tags = ('magento', 'module', 'symmetrics')

# relations
requires = {
    'magento': '*',
    'symmetrics_config_german': '*',
    'symmetrics_module_impressum': '*',
}
excludes = {
}

# who is responsible for this package?
team_leader = {
    'Sergej Braznikov': 'sb@symmetrics.de'
}

# who should check this package in the first place?
maintainer = {
    'Eugen Gitin': 'eg@symmetrics.de'
}

# relative installation path (e.g. app/code/local)
install_path = ''

# additional infos
info = 'symmetrics Rechnungsvorlage'
summary = '''
    Rechtssichere (Deutschland) Vorlage für die Rechnungen
'''
license = 'AFL 3.0'
authors = {
    'Eugen Gitin': 'eg@symmetrics.de'
}
homepage = 'http://www.symmetrics.de'

# files this package depends on
depends_on_files = (
    'app/code/core/Mage/Core/Helper/Abstract.php',
    'app/code/core/Mage/Sales/Model/Order/Pdf/Items/Invoice/Default.php',
    'app/code/core/Mage/Sales/Model/Order/Pdf/Abstract.php',
)
