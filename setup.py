from setuptools import setup

setup(
    name = "pwserverd",
    version = "0.1",
    description = "Password generation and security checking server",
    license = "MIT License",
    long_description = """\
pwserverd provides a way for applications written in PHP and other scripting
languages to efficiently generate and check passwords.
""",
    author = "Alastair Houghton",
    author_email = "alastair@alastairs-place.net",
    url = "http://alastairs-place.net/pwserverd",
    classifiers = [
    'Development Status :: 4 - Beta',
    'Intended Audience :: Developers',
    'License :: OSI Approved :: MIT License',
    'Topic :: System',
    ],
    packages = ['pwserver'],
    package_dir = { 'pwserver': 'pwserver' },
    package_data = { 'pwserver': ['php/*.php'] },
    install_requires = [ 'pwtools>=0.1',
                         'twisted>=9.0.0' ],
    entry_points = {
      'console_scripts': [
      'pwserverd = pwserver:main',
      ]
    }
)
