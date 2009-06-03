import os
import logging
from xml.etree.ElementTree import parse, fromstring

from symmetrics_saasrepo_installer import base, shortcuts


config = None # will be filled by main() with the current config dict
package_dir = os.path.dirname(os.path.abspath(__file__))
logger = logging.getLogger('symmetrics_module_invoicepdf.pre_install')


def main(config_module, info_py):
    '''Is being called by the installer'''
    global config
    config = config_module
    
    package_list = config['package_list']
    
    found = False
    for package in package_list:
        if package['name'] == 'symmetrics_module_trustedshops':
            found = True
            break
    
    if found:
        filename = os.path.join(package_dir, 'build', 'app', 'etc',
                                'modules', 'Symmetrics_InvoicePdf.xml')
        tree = parse(filename)
        elm = tree.findall('modules/Symmetrics_InvoicePdf')[0]
        
        elm.append(fromstring('<depends><Symmetrics_TrustedShops /></depends>'))
        
        tree.write(filename, 'UTF-8')
