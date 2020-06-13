CREATE TABLE IF NOT EXISTS `movil_configuraciongps` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `intervalo` int(5) NOT NULL,
  `plaza` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `movil_dispositivos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imei` varchar(20) NOT NULL,
  `estatus` char(2) NOT NULL,
  `plaza` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `usuario` int(11) NOT NULL,
  `empresa` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `movil_dispositivos_invalidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imei` varchar(20) NOT NULL,
  `mails` int(2) NOT NULL,
  `fecha` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `movil_geocercas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `latitud` varchar(30) NOT NULL,
  `longitud` varchar(30) NOT NULL,
  `radio` int(10) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `fecha` datetime NOT NULL,
  `plaza` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `movil_localizacion` (
  `cve` int(22) NOT NULL AUTO_INCREMENT,
  `latitud` varchar(60) NOT NULL,
  `longitud` varchar(60) NOT NULL,
  `fecha` datetime NOT NULL,
  `imei` varchar(20) NOT NULL,
  `id_geocerca` int(11) NOT NULL,
  PRIMARY KEY (`cve`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `movil_total_ubicados` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `total` int(5) NOT NULL,
  `fecha` datetime NOT NULL,
  `intervalo` int(5) NOT NULL,
  `plaza` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
