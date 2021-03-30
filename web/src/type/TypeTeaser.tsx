import {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import Teaser from '../search/Teaser';
import {generatePath} from 'react-router-dom';
import {Routes} from '../routes';

export default function TypeTeaser(props: { type: ApiRecord.Type.Type }) {
    const {type} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    return (
        <Teaser label={type.name}
                href={generatePath(Routes.TYPE_VIEW, {version: currentVersion.slug, type: type.slug})}
        />
    );
}
