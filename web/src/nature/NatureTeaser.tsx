import {useContext} from 'react';
import AppContext, {AppContextProps} from '../common/Context';
import Teaser from '../search/Teaser';
import {generatePath} from 'react-router-dom';
import {Routes} from '../routes';

export default function NatureTeaser(props: { nature: ApiRecord.Nature.Nature }) {
    const {nature} = props;
    const {currentVersion} = useContext(AppContext) as Required<AppContextProps>;

    return (
        <Teaser label={nature.name}
                href={generatePath(Routes.NATURE_VIEW, {version: currentVersion.slug, nature: nature.slug})}
        />
    );
}
