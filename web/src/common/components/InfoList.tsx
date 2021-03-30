import '../../assets/styles/InfoList.scss';
import {ReactNode} from 'react';
import resolveElementClasses from '../resolveElementClasses';

interface InfoListProps extends Record<string, any> {
    children?: ReactNode
}

function InfoList(props: InfoListProps) {
    const elementProps = Object.assign({}, props, {children: undefined});
    return (
        <table {...elementProps}
               className={resolveElementClasses(props.className, 'pkt-infolist')}>
            <tbody>
            {props.children}
            </tbody>
        </table>
    );
}

interface InfoListItemProps extends Record<string, any> {
    name: string | ReactNode
    children: ReactNode
    nameProps?: Record<string, any>
    valueProps?: Record<string, any>
}

function InfoListItem(props: InfoListItemProps) {
    const rowProps = Object.assign({}, props, {name: undefined, children: undefined});
    const nameProps = props.nameProps ?? {};
    const valueProps = props.valueProps ?? {};
    return (
        <tr {...rowProps}
            className={resolveElementClasses(rowProps.className, 'pkt-infolist-row')}>
            <th {...nameProps}
                scope="row"
                className={resolveElementClasses(nameProps.className, 'pkt-infolist-name')}>
                {props.name}
            </th>
            <td {...valueProps}
                className={resolveElementClasses(valueProps.className, 'pkt-infolist-value')}>
                {props.children}
            </td>
        </tr>
    );
}

InfoList.Item = InfoListItem;
export default InfoList;
