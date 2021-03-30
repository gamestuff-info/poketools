import '../assets/styles/Type.scss';
import {useContext} from 'react';
import {generatePath, Link} from 'react-router-dom';
import {Routes} from '../routes';
import AppContext, {AppContextProps} from '../common/Context';

interface TypeLabelProps extends Record<string, any> {
    type: ApiRecord.Type.Type | ApiRecord.Type.ContestType
    noLink?: boolean
}

export default function TypeLabel(props: TypeLabelProps) {
    const {type} = props;
    const noLink = props.noLink ?? false;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    const classes = props.className ?? [];
    classes.unshift('pkt-type-emblem', `pkt-type-emblem-${type.slug}`);

    const elementProps: Record<string, any> = Object.assign({}, props, {
        className: classes.join(' '),
    });
    delete elementProps.type;
    delete elementProps.noLink;

    const label = (
        <span {...elementProps}>{type.name}</span>
    );
    if (!noLink) {
        return (
            <Link to={generatePath(Routes.TYPE_VIEW, {
                version: currentVersion.slug,
                type: type.slug,
            })}>
                {label}
            </Link>
        );
    }

    return label;
}
