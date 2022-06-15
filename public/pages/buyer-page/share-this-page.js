/* eslint-disable no-undef */
const shareThisPage = async (el) => {
    const canvas = await html2canvas(document.querySelector('#__mainContent'));
    const imgData = canvas.toDataURL('image/png');
    const blobImage = await (await fetch(imgData)).blob();
    const filesArray = [
        new File(
            [blobImage],
            `Order_#${orderPrimaryId}.png`,
            {
                type: blobImage.type,
                lastModified: new Date().getTime()
            }
        )
    ];

    const shareData = {
        files: filesArray
    };

    if (typeof window.navigator.share !== 'undefined') {
        navigator.share(shareData);
    } else {
        const downloadButtonLink = document.createElement('a');

        downloadButtonLink.href = imgData;
        downloadButtonLink.download = `Order_#${orderPrimaryId}.png`;

        document.body.appendChild(downloadButtonLink);

        downloadButtonLink.dispatchEvent(
            new MouseEvent('click', {
                bubbles: true,
                cancelable: true,
                view: window
            })
        );

        document.body.removeChild(link);
    }
};
